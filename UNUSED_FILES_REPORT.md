# تقرير الملفات غير المستخدمة - PHP و Blade

تم إنشاء هذا التقرير بواسطة سكربت تحليل يبحث عن مراجع `view()` و `@include` و `@extends` للـ Blade، وعن استخدام أسماء الكلاسات في المسارات والكود لملفات الـ PHP.

---

## 1. ملفات Blade (`.blade.php`) غير المستخدمة

**المجموع: 2 ملف**

| # | مسار الملف | اسم الـ View |
|---|------------|--------------|
| 1 | `resources/views/loan-structure-trs.blade.php` | `loan-structure-trs` |
| 2 | `resources/views/welcome.blade.php` | `welcome` |

### التفاصيل

- **`loan-structure-trs.blade.php`**: لا يظهر أي استدعاء لـ `view('loan-structure-trs')` أو `@include('loan-structure-trs')` أو `@extends('loan-structure-trs')` في المشروع (باستثناء ذكر في `.phpstorm.meta.php`). **يعتبر غير مستخدم.**

- **`welcome.blade.php`**: لا يوجد أي `view('welcome')` في الكود. في Laravel الافتراضي قد يُستخدم في route الجذر؛ في هذا المشروع لا يوجد استدعاء صريح. **يعتبر غير مستخدم.**

---

## 2. ملفات PHP في `app/` المحتمل أنها غير مستخدمة

**المجموع: 98 ملف**

ملاحظة: بعض الملفات قد تكون مستخدمة عبر:
- تسجيل الأوامر في `app/Console/Kernel.php`
- مسارات المصادقة `Auth::routes()` (Laravel يحمّل Controllers من الـ namespace)
- تضمين مسارات من ملفات مثل `routes/nonBanking.php`, `routes/propertyManagement.php`, `routes/trading.php`
- استخدام ديناميكي (مثلاً `$controller` أو أسماء من قاعدة البيانات)

يُنصح بمراجعة يدوية قبل الحذف.

### 2.1 أوامر Artisan (Console Commands)

| الملف |
|-------|
| `app/Console/Commands/DeleteAllDataFromCompanyCommand.php` |
| `app/Console/Commands/DeleteAllDataFromNonBankingStudyCommand.php` |
| `app/Console/Commands/EmptyCashveroCommand.php` |
| `app/Console/Commands/RefreshAllUsersToDefaultPermissions.php` |
| `app/Console/Commands/RunSqlOnProduction.php` |
| `app/Console/Commands/StartCashingCommand.php` |
| `app/Console/Commands/TestCheckDueAndPastedInvoicesJobCommand.php` |
| `app/Console/Commands/TestCommand.php` |
| `app/Console/Commands/TestConnectionCommand.php` |
| `app/Console/Commands/TestNonBankingBalanceSheet.php` |

**ملاحظة:** إذا كانت هذه الأوامر مسجّلة في `Kernel.php` تحت `$commands` أو `load()`, فهي مستخدمة عند استدعاء `php artisan`.

---

### 2.2 معادلات (Equations)

| الملف |
|-------|
| `app/Equations/OverdraftEndOfMonthInterestCalculation.php` |

---

### 2.3 مساعدات (Helpers)

| الملف |
|-------|
| `app/Helpers/HGlobal.php` |
| `app/Helpers/PropertyLocationHelper.php` |

---

### 2.4 تلميحات (Hints)

| الملف |
|-------|
| `app/Hints/HowToAddOneDimensionReport.php` |

---

### 2.5 Controllers المصادقة (Auth)

| الملف |
|-------|
| `app/Http/Controllers/Auth/ConfirmPasswordController.php` |
| `app/Http/Controllers/Auth/ForgotPasswordController.php` |
| `app/Http/Controllers/Auth/LoginController.php` |
| `app/Http/Controllers/Auth/RegisterController.php` |
| `app/Http/Controllers/Auth/ResetPasswordController.php` |
| `app/Http/Controllers/Auth/VerificationController.php` |

**ملاحظة:** عادةً تُستدعى عبر `Auth::routes()` في `routes/web.php`؛ السكربت لا يربطها تلقائياً. **غالباً مستخدمة.**

---

### 2.6 Controllers أخرى

| الملف |
|-------|
| `app/Http/Controllers/LoansController.php` |
| `app/Http/Controllers/NonBankingServices/AllBranchesMicrofinanceControllerController.php` |
| `app/Http/Controllers/NonBankingServices/ByBranchesMicrofinanceControllerController.php` |
| `app/Http/Controllers/NonBankingServices/ConsolidationController.php` |
| `app/Http/Controllers/NonBankingServices/ConsolidationIncomeStatementController.php` |
| `app/Http/Controllers/NonBankingServices/ConsumerFinanceController.php` |
| `app/Http/Controllers/NonBankingServices/ConsumerfinanceProductsController.php` |
| `app/Http/Controllers/NonBankingServices/ExistingBranchesController.php` |
| `app/Http/Controllers/NonBankingServices/IjaraMortgageController.php` |
| `app/Http/Controllers/NonBankingServices/LeasingCategoriesController.php` |
| `app/Http/Controllers/NonBankingServices/LeasingController.php` |
| `app/Http/Controllers/NonBankingServices/MicrofinanceLoanReportController.php` |
| `app/Http/Controllers/NonBankingServices/MicrofinanceProductMixControllerController.php` |
| `app/Http/Controllers/NonBankingServices/MicrofinanceProductsController.php` |
| `app/Http/Controllers/NonBankingServices/NewBranchFixedAssetsController.php` |
| `app/Http/Controllers/NonBankingServices/NewBranchesMicrofinanceController.php` |
| `app/Http/Controllers/NonBankingServices/PortfolioMortgageController.php` |
| `app/Http/Controllers/NonBankingServices/RecalculateSpreadRateSensitivityController.php` |
| `app/Http/Controllers/NonBankingServices/ReverseFactoringController.php` |
| `app/Http/Controllers/NonBankingServices/SecuritizationController.php` |
| `app/Http/Controllers/NonBankingServices/SpreadsheetController.php` |
| `app/Http/Controllers/PropertyManagements/ForecastedPropertiesController.php` |
| `app/Http/Controllers/PropertyManagements/OccupiedPropertiesWithFullRentCoverageDurationController.php` |
| `app/Http/Controllers/PropertyManagements/OccupiedPropertiesWithPartialRentCoverageDurationController.php` |
| `app/Http/Controllers/PropertyManagements/PropertiesController.php` |
| `app/Http/Controllers/PropertyManagements/PropertiesToBeDeliveredController.php` |
| `app/Http/Controllers/PropertyManagements/PropertyExpensesController.php` |
| `app/Http/Controllers/ViewShareableLinkController.php` |

**ملاحظة:** عدد من Controllers الـ NonBanking و PropertyManagements مُعرّف في `routes/nonBanking.php` و `routes/propertyManagement.php`؛ السكربت يقرأ محتوى المسارات لكن قد لا يطابق كل الصيغ (مثل namespace). يُفضّل التحقق من المسارات يدوياً.

---

### 2.7 Kernel و Middleware

| الملف |
|-------|
| `app/Http/Kernel.php` |

**ملاحظة:** `Kernel.php` مستخدم من قبل Laravel عند التحميل. **يعتبر مستخدماً ولا يُحذف.**

| الملف |
|-------|
| `app/Http/Middleware/RecordLastActivityMiddleware.php` |

---

### 2.8 Form Requests

| الملف |
|-------|
| `app/Http/Requests/BusinessSectorRequest.php` |
| `app/Http/Requests/DeleteContractRequest.php` |
| `app/Http/Requests/NonBankingServices/StoreMicrofinanceBranchAssumption.php` |
| `app/Http/Requests/NonBankingServices/StorePerEmployeeFixedAssetsRequest.php` |
| `app/Http/Requests/SalesForecastRequest.php` |
| `app/Http/Requests/StoreDownPaymentRequest.php` |
| `app/Http/Requests/StoreFixedAssetsForTradingRequest.php` |
| `app/Http/Requests/StoreNewAccountRequest.php` |
| `app/Http/Requests/StorePlanRequest.php` |

---

### 2.9 Interfaces

| الملف |
|-------|
| `app/Interfaces/Models/IHaveIdentifier.php` |
| `app/Interfaces/Models/IHaveName.php` |
| `app/Interfaces/Validators/IValidateModel.php` |

---

### 2.10 Jobs

| الملف |
|-------|
| `app/Jobs/Caches/HandleCashingJob.php` |
| `app/Jobs/DeleteJob.php` |
| `app/Jobs/SalesGatheringSalesSaveJob.php` |
| `app/Jobs/TestJob1.php` |
| `app/Jobs/TestJob2.php` |
| `app/Jobs/TestUploadBatchJob.php` |

---

### 2.11 Models

| الملف |
|-------|
| `app/Models/ContractCashProjection.php` |
| `app/Models/CustomerDueCollectionAnalysis.php` |
| `app/Models/LcAgainstTdOrCdOpeningBalance.php` |
| `app/Models/LcHundredPercentageCashCoverOpeningBalance.php` |
| `app/Models/LgAgainstTdOrCdOpeningBalance.php` |
| `app/Models/LgHundredPercentageCashCoverOpeningBalance.php` |
| `app/Models/MoneyTwo.php` |
| `app/Models/NonBankingService/TestCashFlowStatement.php` |
| `app/Models/NonBankingService/TestIncomeStatement.php` |

---

### 2.12 Repositories

| الملف |
|-------|
| `app/Models/Repositories/BusinessSectorRepository.php` |

---

### 2.13 Traits (Models)

| الملف |
|-------|
| `app/Models/Traits/Accessors/OtherAccessor.php` |
| `app/Models/Traits/Relations/ProfitabilityRelation.php` |
| `app/Models/Traits/Relations/SalesChannelRelation.php` |

---

### 2.14 Policies

| الملف |
|-------|
| `app/Policies/AuthorizeCompanies.php` |

---

### 2.15 ReadyFunctions / Services

| الملف |
|-------|
| `app/ReadyFunctions/CalculatePaybackPeriodService.php` |
| `app/ReadyFunctions/CalculateProfitsEquationsService.php` |
| `app/ReadyFunctions/ConstructionExecutionAndPayment.php` |
| `app/ReadyFunctions/InstallmentMethod.php` |
| `app/ReadyFunctions/IntervalBeginningBalancesService.php` |
| `app/ReadyFunctions/IntervalEndBalancesService.php` |
| `app/ReadyFunctions/InventoryCoverageDays.php` |
| `app/ReadyFunctions/PurchaseInventoryValueBase.php` |
| `app/ReadyFunctions/SupplierPayableEndBalance.php` |
| `app/ReadyFunctions/VariableLoanCalculation.php` |
| `app/ReadyFunctions/qq.php` |

---

### 2.16 Traits عامة

| الملف |
|-------|
| `app/Traits/HasFixedAssetFunding.php` |

---

### 2.17 أخرى

| الملف |
|-------|
| `app/VerificationCode.php` |

---

### 2.18 View Components

| الملف |
|-------|
| `app/View/Components/CustomButtonNameToSubmit.php` |
| `app/View/Components/Submitting.php` |
| `app/View/Components/TableWithAttributes.php` |

---

## 3. كيفية إعادة التشغيل

من مجلد المشروع:

```bash
php find-unused-files.php
```

---

## 4. توصيات

1. **Blade:** الملفان `loan-structure-trs.blade.php` و `welcome.blade.php` يبدوان غير مستخدمين؛ يمكن نقلهما إلى مجلد احتياطي أو حذفهما بعد التأكد من عدم وجود استخدام ديناميكي.
2. **PHP:** مراجعة القوائم أعلاه (خصوصاً Auth و Kernel و Commands و Controllers المربوطة بملفات المسارات) قبل أي حذف.
3. التحقق من استخدام الـ Jobs والـ Models والـ Traits عبر `grep` أو IDE للاسم الكامل للكلاس قبل اعتبارها غير مستخدمة.
