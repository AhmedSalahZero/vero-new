<?php
namespace App\Models\Traits\Scopes;


use App\Helpers\HArr;
use App\Models\NonBankingService\FixedAsset;
use App\ReadyFunctions\CalculateFixedLoanAtBeginningService;
use App\ReadyFunctions\CalculateFixedLoanAtEndService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

trait HasFixedAsset
{

	public function getFixedAssetStructureForFixAssetType(string $fixedAssetType)
    {
        if ($fixedAssetType == FixedAsset::FFE) {
            return $this->generalFixedAssetsFundingStructure;
        } elseif ($fixedAssetType == FixedAsset::NEW_BRANCH) {
            return $this->newBranchFixedAssetsFundingStructure;
        } elseif ($fixedAssetType == FixedAsset::PER_EMPLOYEE) {
            return $this->perEmployeeFixedAssetsFundingStructure;
        }
        dd('not supported fixed asset type');
        // return $this->fixedAssetsFundingStructure->where('fixed_asset_type',$fixedAssetType)->first();
    }
	
} 
