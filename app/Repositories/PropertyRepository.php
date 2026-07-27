<?php 
namespace App\Repositories;

use App\Models\PropertyManagement\Property;
use Illuminate\Database\Eloquent\Collection;

class PropertyRepository 
{
	/**
	 * Store a new property
	 */
	public function store(array $storeData): Property
	{
		return Property::create($storeData);
	}
	
	/**
	 * Update a property
	 */
	public function update(Property $property, array $updateData): Property
	{
		$property->update($updateData);
		return $property->fresh();
	}
	
	/**
	 * Delete a property
	 */
	public function delete(Property $property): bool
	{
		return $property->delete();
	}
	
	/**
	 * Find property by ID
	 */
	public function find(int $id): ?Property
	{
		return Property::find($id);
	}
	
	/**
	 * Get properties by company ID
	 */
	public function getByCompanyId(int $companyId): Collection
	{
		return Property::where('company_id', $companyId)
			->with([ 'country', 'governorate', 'city'])
			->orderBy('created_at', 'desc')
			->get();
	}
	
	/**
	 * Get properties by type and company ID
	 */
	public function getByTypeAndCompanyId(string $type, int $companyId): Collection
	{
		return Property::where('company_id', $companyId)
			->where('nature_id', $type)
			->with([ 'country', 'governorate', 'city'])
			->orderBy('created_at', 'desc')
			->get();
	}
	
	
}
