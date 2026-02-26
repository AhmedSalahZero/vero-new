<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class Bank extends Model
{
	protected $guarded = ['id'];
	public function getName($lang = null)
	{
		$lang = $lang ?: app()->getLocale();
		return $this['name_'.$lang];
	}
	public function getViewName()
	{
		return $this->view_name;
	}
}
