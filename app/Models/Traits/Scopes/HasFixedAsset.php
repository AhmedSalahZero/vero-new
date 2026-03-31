<?php
namespace App\Models\Traits\Scopes;


use App\Models\NonBankingService\FixedAsset;
use App\Models\NonBankingService\Study;


trait HasFixedAsset
{

	public function getFixedAssetStructureForFixAssetType(string $fixedAssetType)
    {
        if ($fixedAssetType == FixedAsset::FFE) {
            return $this->generalFixedAssetsFundingStructure;
        }  elseif ($fixedAssetType == FixedAsset::PER_EMPLOYEE) {
            return $this->perEmployeeFixedAssetsFundingStructure;
        }
		elseif ( $this instanceof Study && $fixedAssetType == FixedAsset::NEW_BRANCH) {
            return $this->newBranchFixedAssetsFundingStructure;
        }
    }
	
} 
