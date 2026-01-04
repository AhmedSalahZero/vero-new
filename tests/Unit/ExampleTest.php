<?php

namespace Tests\Unit;

use App\Models\Company;
use Exception;
use ripcord;
use Tests\TestCase;
require_once(__DIR__ . '/../../public/apis/ripcord.php');

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
		protected string $url ;
	protected String $db;
	protected string $username;
	protected ?string $password ; 
	protected \Ripcord_Client $models;
	protected int $company_id  ;
	protected Company $company ; 
	protected ?int $uid;
	
    public function testBasicTest()
    {
		$company = Company::find(92);
		$this->url = $company->getOdooDBUrl();
		$this->db = $company->getOdooDBName();
		$this->username =$company->getOdooDBUserName();
		$this->password = $company->getOdooDBPassword();
		$this->company_id = $company->id;
		$this->company = $company;
		$currentOdooId = $company->getOdooId() ;
		$common = ripcord::client("$this->url/xmlrpc/2/common");
		$uid = null ;
		try{
			if(is_null($currentOdooId)){
					$uid = $common->authenticate($this->db, $this->username, $this->password, array());
				}else{
					$uid = $currentOdooId ;
				}
		}
		catch(\Exception $e){
			$uid = null;
		}
		if(is_array($uid)){
			$uid = null ;
		}
		if(is_null($currentOdooId)){
			$company->update([
				'odoo_id'=>$uid 
			]);
		}
		$models = ripcord::client("$this->url/xmlrpc/2/object");
		$this->models = $models;
		$this->uid = $uid;
		dd($this->uid);
        $this->assertTrue(true);
    }
}
