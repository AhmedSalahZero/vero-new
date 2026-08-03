<?php 
namespace App\Services\Api\Traits;
use App\Models\Company;
use App\Models\User;
use Exception;
use ripcord;
require_once(public_path('apis/ripcord.php'));

/**
 * @method mixed execute_kw(string $model, string $method, array $args, array $kwargs = [])
 */
trait AuthTrait 
{
	
	protected string $url ;
	protected String $db;
	protected string $username;
	protected ?string $password ; 
	protected \Ripcord_Client $models;
	protected int $company_id  ;
	protected Company $company ; 
	protected ?int $uid;
	/**
	 * * $user اختياري: لو مبعتش بياخد اليوزر اللي عامل لوجن زي الأول
	 * * بنبعته صراحةً لما نكون بنشتغل نيابة عن يوزر تاني
	 * * (السوبر أدمن بيعدّل بيانات يوزر، كيو، كرون، كوماند)
	 */
	public function __construct(Company $company, ?User $user = null )
	{
		$this->url = $company->getOdooDBUrl();
		$this->db = $company->getOdooDBName();
		$user = $user ?: auth()->user();
		/**
		 * @var User|null $user
		 */
		if(!$user instanceof User){
			/**
			 * * قبل كده كان بيضرب "Call to a member function on null"
			 * * من غير ما يقول إن السبب إن مفيش يوزر أصلاً
			 */
			throw new \RuntimeException('Odoo services need a user: pass one explicitly when there is no authenticated user.');
		}
		$this->username =$user->getOdooDBUserName();
		$this->password = $user->getOdooDBPassword();
		$this->company_id = $company->id;
		$this->company = $company;
		$currentOdooId = $user->getOdooId() ;
		$common = ripcord::client("$this->url/xmlrpc/2/common");

		$uid = null ;
		try{
			if(is_null($currentOdooId)){
					$uid = $common->authenticate($this->db, $this->username, $this->password, array());
					if(!is_numeric($uid)){
						/**
						 * * أودو بيرجّع false لو اليوزر أو الباسورد غلط
						 * * وبيرجّع array فيها faultString لو الداتابيز غلط
						 */
						\Illuminate\Support\Facades\Log::warning('Odoo authenticate rejected the credentials', [
							'company_id' => $company->id,
							'odoo_db' => $this->db,
							'odoo_username' => $this->username,
							'response' => $uid,
						]);
						$uid = null;
					}
				}else{
					$uid = $currentOdooId ;
				}
		}
		catch(\Throwable $e){
			/**
			 * * الاستثناء كان بيتبلع هنا خالص
			 * * فمكانش فيه أي طريقة تعرف بيها ليه الاتصال فشل
			 */
			\Illuminate\Support\Facades\Log::error('Odoo authenticate failed: '.$e->getMessage(), [
				'company_id' => $company->id,
				'odoo_url' => $this->url,
				'odoo_db' => $this->db,
				'odoo_username' => $this->username,
				'exception' => get_class($e),
			]);
			$uid = null;

		}
		if(is_array($uid)){
			$uid = null ;
		}
		/**
		 * * بنحفظ الـ id بس لما يكون اتجاب فعلاً
		 * * قبل كده كان بيحفظ null لما المصادقة تفشل
		 */
		if(is_null($currentOdooId) && !is_null($uid)){
			$user->update([
				'odoo_id'=>$uid 
			]);
		}
		$models = ripcord::client("$this->url/xmlrpc/2/object");
		$this->models = $models;
		$this->uid = $uid;
	}
	/**
	 * * الـ uid اللي أودو قبله فعلاً، و null لو المصادقة فشلت
	 */
	public function getUid(): ?int
	{
		return $this->uid;
	}
	   public function execute($model, $method, $args,$kwargs = [])
    {
        $result = $this->models->execute_kw(
            $this->db,
            $this->uid,
            $this->password,
            $model,
            $method,
            $args,
			$kwargs
        );
        if (isset($result['faultCode'])) {
			if(str_contains($result['faultString'], 'TypeError: cannot marshal None unless allow_none is enabled')){
				return ;
			}
         	throw new \Exception($result['faultString']);
        }
        return $result;
    }
	
	// private function executeWithoutThrowException($model, $method, $args)
    // {
    //     $result = $this->models->execute_kw(
    //         $this->db,
    //         $this->uid,
    //         $this->password,
    //         $model,
    //         $method,
    //         $args
    //     );
    //     if (isset($result['faultCode'])) {
    //      //  throw new \Exception($result['faultString']);
	// 		return ;
    //     }
    //     return $result;
    // }
	
	public function fetchData(string $modelName ,array $fields = [],  array $filters = [[]]  )
	{
		$ids=$this->models->execute_kw($this->db, $this->uid, $this->password, $modelName, 'search',$filters );
		return $this->models->execute_kw($this->db, $this->uid, $this->password, $modelName, 'read', array($ids),[
			'fields'=>$fields
		]);
	}
	public function getFieldAcceptableValues($modelName, $fieldName=null)
    {
        try {
            // Step 1: Fetch field metadata from ir.model.fields
            $fields = $this->execute('ir.model.fields', 'search_read', [
                [['model', '=', $modelName]
				// , ['name', '=', $fieldName]
			],
                ['name', 'ttype', 'selection', 'relation', 'required', 'domain'],
                // ['limit' => 1]
            ]);
            if (empty($fields)) {
                throw new Exception("Field {$fieldName} not found in model {$modelName}");
            }

            $field = $fields[0];
            $result = [
                'model' => $modelName,
                'field' => $fieldName,
                'type' => $field['ttype'],
                'required' => $field['required'],
                'acceptable_values' => [],
                'description' => ''
            ];

            // Step 2: Handle field type
            switch ($field['ttype']) {
                case 'selection':
                    // Parse selection options
                    if ($field['selection']) {
                        // Selection is stored as a string like "[['draft', 'Draft'], ['posted', 'Posted']]"
                        $selection = eval("return " . $field['selection'] . ";"); // Convert string to array
                        $result['acceptable_values'] = array_map(function ($option) {
                            return [
                                'value' => $option[0],
                                'label' => $option[1]
                            ];
                        }, $selection);
                        $result['description'] = 'Select one of the predefined options.';
                    } else {
                        $result['description'] = 'Selection field, but no options defined (possibly dynamic).';
                    }
                    break;

                case 'many2one':
                    // Fetch records from the relation model
                    if ($field['relation']) {
                        $records = $this->execute($field['relation'], 'search_read', [
                            [], // No domain filter by default
                            ['id', 'name'],
                            ['limit' => 100] // Limit to avoid large datasets
                        ]);
                        $result['acceptable_values'] = array_map(function ($record) {
                            return [
                                'id' => $record['id'],
                                'name' => $record['name']
                            ];
                        }, $records);
                        $result['description'] = "Select an ID from the {$field['relation']} model. Additional records may exist beyond the limit.";
                    } else {
                        $result['description'] = 'Many2one field with no relation model defined.';
                    }
                    break;

                case 'many2many':
                case 'one2many':
                    $result['acceptable_values'] = [];
                    $result['description'] = "Relational field ({$field['ttype']}) linking to {$field['relation']}. Provide a list of IDs from {$field['relation']} (many2many) or create related records (one2many).";
                    break;

                case 'char':
                case 'text':
                    $result['acceptable_values'] = [];
                    $result['description'] = 'Free-form text input. May be constrained by domain or custom validation.';
                    break;

                case 'integer':
                    $result['acceptable_values'] = [];
                    $result['description'] = 'Integer number. May be constrained by domain or custom validation.';
                    break;

                case 'float':
                    $result['acceptable_values'] = [];
                    $result['description'] = 'Decimal number. May be constrained by domain or custom validation.';
                    break;

                case 'boolean':
                    $result['acceptable_values'] = [
                        ['value' => true, 'label' => 'True'],
                        ['value' => false, 'label' => 'False']
                    ];
                    $result['description'] = 'Boolean value (True or False).';
                    break;

                case 'reference':
                    $result['acceptable_values'] = [];
                    $result['description'] = 'Dynamic reference to various models. Format: <model>,<id> (e.g., res.partner,1).';
                    break;

                default:
                    $result['acceptable_values'] = [];
                    $result['description'] = "Field type {$field['ttype']} not explicitly handled. Consult Odoo documentation for constraints.";
                    break;
            }

            // Add domain if present
            if ($field['domain']) {
                $result['domain'] = $field['domain'];
                $result['description'] .= " Constrained by domain: {$field['domain']}.";
            }

            return $result;
        } catch (Exception $e) {
            throw new Exception("Failed to fetch field values: " . $e->getMessage());
        }
    }
	
	//   protected function getAnalysisAccountIds(array $analytic_distribution,?int $partnerId = null):array
    // {
	// 	if (is_null($partnerId)) {
    //         return [[6, 0, []]];
    //     }
    //     $distribution_analytic_account_ids = [];
    //     foreach (array_keys($analytic_distribution) as $key) {
    //         if ($key > 0) {
    //             $distribution_analytic_account_ids[] = [0, (int)$key];
    //         }
    //     }
    //     // Wrap in outer array with 6 and 0
    //     if (count($distribution_analytic_account_ids)) {
    //         $distribution_analytic_account_ids = [[6, 0, ...$distribution_analytic_account_ids]];
    //     } else {
    //         $distribution_analytic_account_ids = [[6, 0, []]];
    //     }
    //     return $distribution_analytic_account_ids;
    // }
	
}
