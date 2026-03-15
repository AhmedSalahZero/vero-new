<?php 
namespace App\Models ; 

use Illuminate\Database\Eloquent\Model;

/**
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\MoneyTwo newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\MoneyTwo newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\MoneyTwo query()
 * @mixin \Eloquent
 */
class MoneyTwo  extends Model
{
	protected $table = 'money2';
	public $timestamps = false ;
	
}
