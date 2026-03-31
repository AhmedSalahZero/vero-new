<?php
namespace App\Models\Traits\Accessors ;
trait ServiceItemAccessor
{
    public function getId():int
    {
        return $this->id ; 
    }
    public function getName():string 
    {
        return $this->name;
    }
  
    public function getCreatorName():string
    {
        return $this->creator->name ?? __('N/A');
    }
}
