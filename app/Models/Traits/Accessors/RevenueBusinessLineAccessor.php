<?php
namespace App\Models\Traits\Accessors ;
trait RevenueBusinessLineAccessor
{
    public function getId():int
    {
        return $this->id ; 
    }
    public function getName():string 
    {
        return $this->name;
    }
    public function getCategoryName()
    {
        // need to by changed
        return $this->name ;
    }
    
    public function getServiceItemName()
    {
        // need to by changed
        return $this->name ;
    }
   
    public function getCreatorName():string
    {
        return $this->creator->name ?? __('N/A');
    }
}
