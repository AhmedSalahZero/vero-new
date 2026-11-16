<?php

namespace App\Rules;

use App\Models\Company;
use App\Models\LetterOfGuaranteeIssuance;
use App\Models\Partner;
use Carbon\Carbon;
use Illuminate\Contracts\Validation\Rule;
use Throwable;

/**
 * * المستفيد لازم يكون مربوط بأودو (odoo_id) لو الخطاب هيتبعت لأودو
 *
 * * من غير الفحص ده الخطاب كان بيتحفظ عادي ، و بعد الـ commit
 * * LetterOfGuaranteeService::createLgIssuanceCashCover كان بيضرب TypeError
 * * لأن $odooPartnerId = null ، فقيود الـ cash cover و المصاريف و العمولة
 * * ماكانتش بتروح أودو (خطاب الضمان 204 في الشركة 92)
 *
 * * الشروط هي نفس شروط handleLgIssuanceCashCoverForOdoo و
 * * handleIssuanceAndCommissionFeesForOdoo ، فأي خطاب مش هيتبعت لأودو
 * * مش بيتأثر
 */
class BeneficiaryLinkedToOdooRule implements Rule
{
    protected string $message = '';

    public function __construct(
        protected ?Company $company,
        protected ?string $categoryName,
        protected ?string $issuanceDate
    ) {
    }

    public function passes($attribute, $value): bool
    {
        if (! $this->company) {
            return true;
        }

        /**
         * * find() على array بيرجّع Collection مش partner
         */
        $partner = is_numeric($value)
            ? Partner::where('company_id', $this->company->id)->find($value)
            : null;

        if (! $partner) {
            $this->message = __('The selected beneficiary is invalid.');

            return false;
        }

        if (! $this->willBeSentToOdoo() || $partner->getOdooId()) {
            return true;
        }

        $this->message = __('The beneficiary :name is not linked to Odoo. Add it in Odoo, run "Read Odoo Partners", then select it again.', [
            'name' => $partner->getName(),
        ]);

        return false;
    }

    public function message(): string
    {
        return $this->message;
    }

    protected function willBeSentToOdoo(): bool
    {
        if ($this->categoryName === LetterOfGuaranteeIssuance::OPENING_BALANCE) {
            return false;
        }

        $issuanceDate = $this->formattedIssuanceDate();

        /**
         * * التاريخ الغلط أو الفاضي ليه فحصه الخاص (issuance_date required)
         */
        if (is_null($issuanceDate)) {
            return false;
        }

        return $this->company->hasOdooIntegrationCredentials()
            && $this->company->withinIntegrationDate($issuanceDate);
    }

    protected function formattedIssuanceDate(): ?string
    {
        if (! $this->issuanceDate) {
            return null;
        }

        try {
            return Carbon::make($this->issuanceDate)?->format('Y-m-d');
        } catch (Throwable) {
            return null;
        }
    }
}
