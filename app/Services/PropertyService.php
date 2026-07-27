<?php

namespace App\Services;

use App\Http\Requests\StorePropertyRequest;
use App\Models\Company;
use App\Models\PropertyManagement\Property;

use App\Repositories\PropertyRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PropertyService
{
	// Property type constants
	// private const TYPE_UNIT = 'unit';
	// private const TYPE_LAND = 'land';
	// private const TYPE_COMPLEX = 'complex';
	// private const TYPE_BUILDING = 'building';
	

	// Fields to exclude from storeBasicForm
	private const EXCLUDED_FIELDS = ['_token', 'save', '_method'];
	
	// Database connection name
	
	protected $propertyRepository;
	
	public function __construct(PropertyRepository $propertyRepository)
	{
		$this->propertyRepository = $propertyRepository;
	}
 
    public function store(StorePropertyRequest $request, Company $company, ?Property $property = null): Property
    {
        return DB::transaction(function () use ($request, $company, $property) {
            // Create new property instance
            $property = is_null($property) ? new Property() : $property;
            
            // Store basic form data (name, code, nature_id, ownership_id, country_id, etc.)
            $property = $property->storeBasicForm($request, self::EXCLUDED_FIELDS, PROPERTY_MANAGEMENT_CONNECTION_NAME);
            
            // Store non-repeater relations (country, governorate, city, ownership, nature)
            $property->storeRelationsWithNoRepeater($request, $company);
            
            // Handle property-specific relations based on type
            // $natureId = $request->input('nature_id', self::TYPE_UNIT);
            // $this->handlePropertyTypeRelations($property, $request, $natureId, $company);
            
            return $property;
        });
    }

    /**
     * Update an existing property
     * 
     * @param Request $request
     * @param Property $property
     * @param Company $company
     * @return Property
     */
	
    public function update(Request $request, Property $property, Company $company): Property
    {
        return DB::transaction(function () use ($request, $property, $company) {            
            $property = $property->storeBasicForm($request, self::EXCLUDED_FIELDS,PROPERTY_MANAGEMENT_CONNECTION_NAME);
            $property->storeRelationsWithNoRepeater($request, $company);
            $property->refresh(); 
            return $property->refresh();
        });
    }

    
    /**
     * Delete a property
     * 
     * @param Property $property
     * @return bool
     */
    public function delete(Property $property): bool
    {
        return DB::transaction(function () use ($property) {
            // Delete child units first
            $property->units()->delete();
            
           
            
            return $property->delete();
        });
    }

 
    public function getModelDataFormatted(Company $company, ?Property $property): array
    {
		
		$baseData = Property::getPropertyFormatted($company, $property);
		foreach($property && count($property->units)? $property->units : [null] as $unit) {
			$baseData['units'][] = Property::getPropertyFormatted($company, $unit);
		}
		return $baseData;
    }
	

}
