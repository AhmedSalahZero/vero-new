<?php 
namespace App\Services\Api\Traits;

trait CommonHelper 
{
	/**
	 * * $chartOfAccountId بقي nullable: syncBranchSafe() بتبعت null لما
	 * * مايكونش فيه حساب مطابق في اودو، وكان بيرمي TypeError.
	 *
	 * * ولو null بنرجع null علي طول من غير ما نسأل اودو — لان الاستعلام
	 * * بـ default_account_id = null بيرجع اليوميات اللي مالهاش حساب
	 * * افتراضي وياخد اول واحدة فيهم، يعني يومية غلط خالص.
	 */
	public function getJournalIdFromChartOfAccountId(?int $chartOfAccountId):?int
	{
		if (is_null($chartOfAccountId)) {
			return null;
		}
		return $this->fetchData('account.journal',[],[[['default_account_id','=',$chartOfAccountId]]])[0]['id']??null;
	}
	
}
