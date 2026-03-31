<?php
namespace App\Models\Traits\Accessors ;

use App\Helpers\HStr;

trait SharingLinkAccessor
{
    public function getId():int
    {
        return $this->id ; 
    }
    public function getName():string 
    {
        return $this->user_name ?: __('N/A');
    }
    public function getLink():string 
    {
        return $this->link;
    }
    public function getSharableTypeName():string {
        return HStr::getLastWordInString($this->shareable_type,'\\');
    }
    public function isActive():bool 
    {
        return (bool)$this->is_active ;
    }
  
    public function getCreatorName():string
    {
        return $this->creator->name ?? __('N/A');
    }
}
