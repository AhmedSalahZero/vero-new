<?php 
namespace App\Models ; 

use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperMoney
 */
class Money  extends Model
{
	protected $table = 'money';
	public $timestamps = false ;
	
}
