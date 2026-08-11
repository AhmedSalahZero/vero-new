<?php

namespace App\Http\Requests;

use App\Rules\UniqueToCompanyAndAdditionalColumnsRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * * الاسم لازم يكون unique للبنك الواحد في الشركة الواحدة
 * * (company_id + financial_institution_id + name) — نفس اسم التسهيل
 * * مسموح يتكرر مع بنك تاني
 *
 * * بناخد البنك و السجل اللي بنعدله من الـ route مش من input مخفي في الفورم ،
 * * عشان الشرط ما يتلغيش لو حد بعت الريكوست من غير الحقول دي
 */
class StoreLetterOfGuaranteeFacilityRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $financialInstitution = $this->route('financialInstitution');
        $letterOfGuaranteeFacility = $this->route('letterOfGuaranteeFacility');

        return [
            'name' => [
                'required',
                new UniqueToCompanyAndAdditionalColumnsRule(
                    'LetterOfGuaranteeFacility',
                    'name',
                    $letterOfGuaranteeFacility ? $letterOfGuaranteeFacility->id : 0,
                    [['financial_institution_id', '=', $financialInstitution ? $financialInstitution->id : 0]],
                    __('This Letter Of Guarantee Facility Already Exist')
                ),
            ],
        ];
    }
}
