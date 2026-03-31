<?php

namespace App\Models\Traits\Accessors;

use App\Helpers\HVero;
use Carbon\Carbon;

trait FinancialStatementAccessor
{

	public function getId(): int
	{
		return $this->id;
	}
	public function getName(): string
	{
		return $this->name;
	}
	public function getDurationType(): string
	{
		return $this->duration_type;
	}
	
	public function getCreatorName(): string
	{
		return $this->creator->name ?? __('N/A');
	}
	public function getStartDate(){
		return $this->start_from;
	}
	public function getStartDateFormatted(){
		return $this->getStartDate() ? Carbon::make($this->getStartDate())->format('d-m-Y') : null;
	}
	public function getDuration()
	{
		return $this->duration ;
	}
	public function getIntervalFormatted(): array
	{
		$method = 'addMonth';
		$startDate = Carbon::make($this->start_from);
		if ($this->duration_type == 'annually') {
			$method = 'addYear';
			$endDate = Carbon::make($this->start_from)->addYears($this->duration);
		} elseif ($this->duration_type == 'quarterly') {
			$endDate = Carbon::make($this->start_from)->addMonths(($this->duration) - 1);
			$dateBetweenTwoIntervals = generateDatesBetweenTwoDates($startDate, $endDate, $method, 'M\'Y', false, 'Y-m-d');
			return HVero::formatDateIntervalFor($dateBetweenTwoIntervals, $this->duration_type);
		} else {
			$endDate = Carbon::make($this->start_from)->addMonths(($this->duration) - 1);
		}
		return \generateDatesBetweenTwoDates($startDate, $endDate, $method, 'M\'Y', false, 'Y-m-d');
	}
	// public function hasMainRowPayload(int $financialStatementItemId): bool
	// {
	// 	return $this->subItems()->wherePivot('financial_statement_item_id', $financialStatementItemId)->wherePivot('sub_item_name', null)->exists();
	// }

	
	public function canEditDurationType(): bool
	{
		$incomeStatement = $this->incomeStatement;
		if(!$incomeStatement){
			return false ;
		}
		return  ! $incomeStatement->can_view_actual_report;
	
	}
}
