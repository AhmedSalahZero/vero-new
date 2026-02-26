<?php
namespace App\Traits\Models;

use Carbon\Carbon;



trait HasUserComment
{
	public function getUserComment():?string 
	{
		return $this->user_comment ?: '' ;
	}
	public function hasComment():bool
	{
		return (bool) $this->getUserComment(); 
	}
	
}
