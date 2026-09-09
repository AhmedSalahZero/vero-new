<?php

namespace App\Exports\Statements;

/**
 * BankStatementExport
 * ------------------------------------------------------------------
 * تصدير كشف الحساب البنكي كامل — كل الصفوف مش الصفحة المعروضة بس.
 *
 * الأعمدة الافتراضية في AbstractStatementExport (Limit / Actual Limit /
 * Beginning Balance / Debit / Credit / Room / Calculated Interest ،
 * و End Balance للتلوين حسب الإشارة) هي نفسها أعمدة الشاشة دي بالظبط ،
 * فمفيش حاجة تتظبط هنا.
 */
class BankStatementExport extends AbstractStatementExport
{
}
