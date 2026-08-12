# دورة إصدار الاعتماد المستندي (Letter of Credit Issuance)

> شرح كامل للـ cycle من الصفر — للمبرمج الجديد على المشروع أو للمحاسب اللي عايز يفهم النظام بيعمل إيه ورا الكواليس.
> كل جزء فيه مثال واقعي بأرقام.
>
> **آخر تحديث:** 2026-08-12 · **الكود المرجعي:** `LetterOfCreditIssuanceController` و `LetterOfCreditIssuance` و `resources/views/reports/LetterOfCreditIssuance/`

---

## المحتويات

1. [الاعتماد المستندي إيه أصلاً؟ (خلفية للي مش عارف)](#١-الاعتماد-المستندي-إيه-أصلاً)
2. [الأربع أنواع (Sources) والفرق بينهم](#٢-الأربع-أنواع-sources)
3. [رحلة الاعتماد من أول لآخر](#٣-رحلة-الاعتماد-من-أول-لآخر)
4. [شرح كل حقل في الفورم](#٤-شرح-كل-حقل-في-الفورم)
5. [الكشوف (Statements) اللي بتتكتب](#٥-الكشوف-statements-اللي-بتتكتب)
6. [الـ Popups والأزرار](#٦-الـ-popups-والأزرار)
7. [مثال واقعي كامل من أول لآخر](#٧-مثال-واقعي-كامل)
8. [المشاكل اللي لقيتها](#٨-المشاكل-اللي-لقيتها)

---

## ١. الاعتماد المستندي إيه أصلاً؟

تخيّل إنك شركة في مصر عايزة تستورد ماكينة من الصين بـ **100,000 دولار**.

المشكلة: المورّد الصيني مش عارفك ومش واثق إنك هتدفع. وانت مش واثق إنه هيبعت البضاعة. مين يبدأ؟

**الحل: البنك بيقف في النص.** بتروح لبنك مصري وتقوله: "اعمللي اعتماد مستندي لصالح المورّد الصيني بـ 100,000 دولار". البنك بيبعت للمورّد تعهّد مكتوب معناه:

> "أنا البنك أتعهّد إني أدفعلك الـ 100,000 دولار **لو** قدّمتلي المستندات اللي تثبت إنك شحنت البضاعة."

المورّد بقى مطمّن — مش واثق فيك انت، لكن واثق في البنك. وانت مطمّن — البنك مش هيدفع غير لما تيجي المستندات.

### البنك بياخد إيه مقابل ده؟

| البند | إيه هو | مثال |
|---|---|---|
| **مصاريف إصدار** (Issuance Fees) | رسم ثابت مرة واحدة على فتح الاعتماد | 5,000 جنيه |
| **عمولة** (Commission) | نسبة من قيمة الاعتماد | 0.5% من 100,000$ |
| **تغطية نقدية** (Cash Cover) | مبلغ بيجمّده من حسابك كضمان | 20% = 20,000$ |

### التغطية النقدية دي بالذات مهمة تفهمها

البنك بيقول: "أنا هتعهّد بـ 100,000 دولار نيابةً عنك. لو انت مدفعتش، أنا اللي هدفع. فعشان أقلّل مخاطرتي، هجمّد جزء من فلوسك عندي دلوقتي."

- **تغطية 100%** يعني البنك مجمّد كل المبلغ → البنك مش مخاطر بأي حاجة
- **تغطية 20%** يعني البنك مخاطر بـ 80,000$ → فبياخد عمولة أعلى
- الجزء اللي البنك مخاطر بيه بيتخصم من **حد التسهيل** (LC Facility Limit) بتاعك

### الأنواع (LC Types)

| النوع | معناه |
|---|---|
| **Sight LC** | الدفع فوري أول ما المستندات توصل |
| **Deferred LC** | الدفع مؤجّل (مثلاً بعد 90 يوم من الشحن) |

النظام بيفصل بينهم لأن كل نوع ليه رصيد مستخدم منفصل من التسهيل.

---

## ٢. الأربع أنواع (Sources)

النظام بيدعم **٤ طرق** مختلفة تفتح بيها اعتماد. كل واحدة ليها **فورم منفصل** وURL منفصل:

```
/en/{company}/letter-of-credit-issuance/create/{source}
```

| الـ source | اسم الفورم | يعني إيه |
|---|---|---|
| `lc-facility` | `lc-facility-form.blade.php` | من **حد تسهيل** متفق عليه مع البنك |
| `hundred-percentage-cash-cover` | `hundred-percentage-cash-cover-form.blade.php` | **تغطية 100%** — مفيش تسهيل، انت مغطي كل المبلغ |
| `against-cd` | `against-cd-form.blade.php` | مضمون بـ **شهادة استثمار** عندك في البنك |
| `against-td` | `against-td-form.blade.php` | مضمون بـ **وديعة لأجل** عندك في البنك |

### الفرق العملي

**مثال:** عايز اعتماد بـ 100,000$.

- **`lc-facility`**: البنك مديك حد 5 مليون جنيه. الاعتماد ده هياكل منهم. لو التغطية 20%، البنك مخاطر بـ 80% → بيتخصم 80% من الحد.
- **`hundred-percentage-cash-cover`**: بتجمّد 100,000$ كاملة. البنك مش مخاطر بحاجة → مش محتاج تسهيل أصلاً. عشان كده حقل `cash_cover_rate` **مقفول على 100 و readonly**.
- **`against-cd` / `against-td`**: عندك شهادة استثمار أو وديعة بـ 150,000$ في البنك. البنك بيرهنها ويفتحلك الاعتماد. الفرق الوحيد في الكود: بيظهر حقلين زيادة (`cd_or_td_account_type_id` و `cd_or_td_id`) عشان تختار أنهي شهادة/وديعة بالظبط.

> **في الكود** — `commonViewVars()` هي اللي بتقرر: لو الـ source هو `against-cd` بيجيب `AccountType::onlyCdAccounts()`، ولو `against-td` بيجيب `onlyTdAccounts()`. وبيحدد عملة الشهادة/الوديعة تلقائياً.

---

## ٣. رحلة الاعتماد من أول لآخر

```
   [١] إنشاء                [٢] شغّال              [٣] تم الدفع
   ┌──────────┐            ┌──────────┐          ┌──────────┐
   │  create  │ ─────────► │ running  │ ───────► │   paid   │
   └──────────┘   store    └──────────┘ markAsPaid└──────────┘
                                 ▲                     │
                                 └─────────────────────┘
                                    backToRunning
                                    (تراجع عن الدفع)
```

### المرحلة ١: الإنشاء (`store`)

بتملا الفورم وتضغط حفظ. الكود بيعمل **٦ حاجات في ترانزاكشن واحدة**:

| # | إيه اللي بيحصل | فين بيتكتب |
|---|---|---|
| 1 | بيحفظ الاعتماد نفسه | `letter_of_credit_issuances` |
| 2 | بيخصم **التغطية النقدية** من حسابك الجاري | `current_account_bank_statements` |
| 3 | بيخصم **مصاريف الإصدار** من حسابك الجاري | `current_account_bank_statements` |
| 4 | بيسجّل **قيمة الاعتماد** في كشف الاعتمادات | `letter_of_credit_statements` |
| 5 | بيسجّل **التغطية** في كشف التغطيات | `letter_of_credit_cash_cover_statements` |
| 6 | بيخصم **العمولة** من حسابك الجاري | `current_account_bank_statements` |

> **مهم:** كل ده جوه `OdooSync::transaction(...)` — يعني لو أي خطوة ضربت، **كله بيترجع**. مفيش حالة إن الاعتماد يتحفظ من غير حركاته.

### المرحلة ٢: شغّال (`running`)

الاعتماد قاعد مستني. في المرحلة دي تقدر:
- تعدّله (`update`)
- تضيفله **مصاريف إضافية** (`applyExpense`) — زي مصاريف شحن أو تليكس
- تمسحه (`destroy`)

### المرحلة ٣: تم الدفع (`markAsPaid`)

المستندات وصلت والبنك دفع للمورّد. بتفتح popup وتقول للنظام "خلاص اتدفع". الكود بيعمل:

1. بيغيّر الحالة لـ `paid` ويسجّل تاريخ الدفع وفاتورة المورّد
2. **بيقفل** قيمة الاعتماد في كشف الاعتمادات (يرجّع الحد المستهلك)
3. **بيفكّ** التغطية النقدية المجمّدة
4. بيسجّل الفوايد لو فيه
5. **وهنا القرار المهم:** مين دفع الباقي؟

```
diffBetweenLcAmountAndCashCover = قيمة الاعتماد − التغطية النقدية
```

| الاختيار | إيه اللي بيحصل |
|---|---|
| **Financed by Bank** (البنك موّل) | الفرق بيتسجل كـ **مديونية عليك** في `lc_overdraft_bank_statements` — يعني البنك دفع بدالك وبقى ليه فلوس عندك |
| **Financed by Self** (انت دفعت) | الفرق بيتخصم من **حسابك الجاري** في `current_account_bank_statements` |

**مثال:** اعتماد 100,000$ بتغطية 20,000$.
- لو **البنك موّل**: 80,000$ بتتسجّل مديونية عليك للبنك
- لو **انت دفعت**: 80,000$ بتتخصم من حسابك الجاري على طول

### التراجع (`backToRunning`)

لو دوست "تم الدفع" بالغلط، فيه زرار بيرجّعه `running` وبيمسح كل الحركات اللي اتعملت في خطوة الدفع.

### التعديل (`update`) — انتبه للطريقة دي

التعديل **مش** بيعدّل. هو بيعمل:

```php
$letterOfCreditIssuance->deleteAllRelations();   // امسح كل الحركات
$letterOfCreditIssuance->delete();               // امسح الاعتماد نفسه
$this->storeWithinTransaction(...);              // اعمله من الأول
```

يعني **الاعتماد بياخد id جديد كل مرة تعدّله**. ده بيحل مشكلة إن الحركات القديمة تفضل متعلّقة، لكن بيسبّب مشاكل تانية (شوف [قسم المشاكل](#٨-المشاكل-اللي-لقيتها)).

---

## ٤. شرح كل حقل في الفورم

### المجموعة الأولى: التصنيف والبنك

| الحقل | الاسم في الكود | فايدته | تأثيره في الكود |
|---|---|---|---|
| **Category** | `category_name` | `new-issuance` (اعتماد جديد) أو `opening-balance` (رصيد افتتاحي) | **مهم جداً**: لو `opening-balance` الكود **مش** بيخصم التغطية ولا مصاريف الإصدار ولا العمولة. لأنها حركات حصلت قبل ما تدخل على النظام |
| **Transaction Name** | `transaction_name` | اسم وصفي للعملية | بيتحط في **كل** التعليقات على الحركات، عشان تعرف الحركة دي بتاعت أنهي اعتماد |
| **Financial Institution** | `financial_institution_id` | البنك | بيتخزن على كل صف في الكشوف — الكشف بيتحسب **لكل بنك على حدة** |
| **LC Facility** | `lc_facility_id` | أنهي تسهيل | يظهر في `lc-facility` بس. لو مختارتش → رسالة "No Available Letter Of Credit Facility Found" |
| **LC Limit** | `limit` | حد التسهيل | **للعرض بس** — بيتجاب بالـ JS من التسهيل المختار |
| **Total LCs Outstanding Balance** | `total_lc_outstanding_balance` | إجمالي المستخدم من كل الأنواع | للعرض بس |
| **LC Type** | `lc_type` | `sight_lc` أو `deferred_lc` | بيقسّم الكشف — كل نوع ليه سلسلة رصيد منفصلة في الـ trigger |
| **LC Type Outstanding Balance** | `lc_type_outstanding_balance` | المستخدم من النوع ده | للعرض بس |
| **LC Code** | `lc_code` | رقم الاعتماد من البنك | للعرض والبحث. **مفيش فحص إنه unique** |

### المجموعة التانية: المورّد والتعاقد

| الحقل | الاسم في الكود | فايدته | تأثيره في الكود |
|---|---|---|---|
| **Beneficiary** | `partner_id` | المستفيد (المورّد) | بيظهر اسمه في التعليقات. `Partner::onlySuppliers()` |
| **New Customer Name** | `new_customer_name` | تضيف مورّد جديد من غير ما تسيب الصفحة | |
| **Contract** | `contract_id` | التعاقد المرتبط | **قيم خاصة**: `-1` = "PO جديد" → بيعمل PurchaseOrder جديد. `-2` = "PO موجود" → بيربط بواحد قديم |
| **Purchase Order** | `purchase_order_id` | أمر الشراء | بيتحدد بالـ JS حسب التعاقد |
| **New PO Number** | `new_purchase_order_number` | رقم PO جديد | لو `contract_id == -1` والرقم مش موجود → بيتعمل PO جديد |
| **Purchase Order Date** | `purchase_order_date` | تاريخ أمر الشراء | للعرض والبحث |
| **Transaction Reference** | `transaction_reference` | مرجع داخلي | للعرض |
| **Transaction Date** | `transaction_date` | تاريخ العملية | للعرض — **مش** بيتحسب عليه أي حركة |

### المجموعة التالتة: التواريخ والمبالغ

| الحقل | الاسم في الكود | فايدته | تأثيره في الكود |
|---|---|---|---|
| **Issuance Date** | `issuance_date` | تاريخ فتح الاعتماد | **أهم تاريخ في الشاشة** — كل الحركات الست بتتسجّل بيه |
| **LC Duration (Days)** | `lc_duration_days` | مدة الاعتماد بالأيام | بيحسب `due_date` بالـ JS بس. **تأثيره على العمولة معطّل** (شوف المشاكل) |
| **Due Date** | `due_date` | تاريخ الاستحقاق | `readonly` — بيتحسب = `issuance_date + lc_duration_days` |
| **LC Amount** | `lc_amount` | قيمة الاعتماد بعملته | **الحقل الوحيد اللي عليه فاليديشن** (`required, gt:0`) |
| **LC Currency** | `lc_currency` | عملة الاعتماد | |
| **Exchange Rate** | `exchange_rate` | سعر الصرف | `getLcAmountInMainCurrency() = exchange_rate × lc_amount`. لو فاضي بيتعامل كـ 1 |
| **Amount In Payment Currency** | `amount_in_main_currency` | القيمة بالعملة الأساسية | `readonly` — دي القيمة اللي بتتسجّل في كشف الاعتمادات |

**مثال:** اعتماد بـ 100,000 دولار وسعر الصرف 48.5 → `amount_in_main_currency = 4,850,000` جنيه. ده الرقم اللي بيتخصم من التسهيل.

### المجموعة الرابعة: التغطية النقدية

| الحقل | الاسم في الكود | فايدته | تأثيره في الكود |
|---|---|---|---|
| **Cash Cover Rate %** | `cash_cover_rate` | نسبة التغطية | في `hundred-percentage-cash-cover` بيبقى **100 و readonly** |
| **Cash Cover Currency** | `lc_cash_cover_currency` | عملة التغطية | بتتخزن على صفوف الكشف — التغطية ممكن تكون بعملة غير عملة الاعتماد |
| **Cash Cover Amount** | `cash_cover_amount` | مبلغ التغطية | `readonly` — بيتحسب بالـ JS = `amount_in_main_currency × cash_cover_rate / 100` |
| **Deducted From (Type)** | `cash_cover_deducted_from_account_type` | نوع الحساب | بيفلتر قائمة الحسابات بالـ JS |
| **Deducted From (Account)** | `cash_cover_deducted_from_account_id` | رقم الحساب | الحساب اللي هيتخصم منه. لو مبعتش → بيرجع لحساب المصاريف |

### المجموعة الخامسة: العمولات والمصاريف

| الحقل | الاسم في الكود | فايدته | تأثيره في الكود |
|---|---|---|---|
| **LC Commission Rate %** | `lc_commission_rate` | نسبة العمولة | |
| **LC Commission Amount** | `lc_commission_amount` | مبلغ العمولة | `readonly` — بيتحسب بالـ JS |
| **Min LC Commission Fees** | `min_lc_commission_fees` | الحد الأدنى للعمولة | **`max($min, $commission)`** — البنك بياخد الأكبر فيهم |
| **Issuance Fees** | `issuance_fees` | مصاريف الإصدار | بيتخصم مرة واحدة |
| **Fees Account Type / Account** | `lc_fees_and_commission_account_type` / `lc_fees_and_commission_account_id` | حساب المصاريف | العمولة والمصاريف بتتخصم من هنا |

**مثال على الحد الأدنى:** عمولة 0.5% على 4,850,000 = 24,250 جنيه. الحد الأدنى 500 جنيه. → `max(500, 24250) = 24,250`. لكن لو الاعتماد كان صغير (50,000 جنيه) → العمولة 250 والحد الأدنى 500 → **البنك بياخد 500**.

### المجموعة السادسة: التمويل

| الحقل | الاسم في الكود | فايدته | تأثيره في الكود |
|---|---|---|---|
| **Financed By** | `financed_by_bank_or_self` | مين هيدفع الباقي | **بيغيّر مسار الكود بالكامل وقت الدفع** — إما مديونية في `lc_overdraft` أو خصم من الحساب الجاري |
| **Financing Duration (Days)** | `financing_duration` | مدة التمويل | بيظهر في تعليق حركة الدفع ("Post Finance [90] Days") |

### حقول خاصة بـ against-cd / against-td

| الحقل | فايدته |
|---|---|
| `cd_or_td_account_type_id` | نوع الضمان: شهادة (29) ولا وديعة (28) |
| `cd_or_td_id` | أنهي شهادة/وديعة بالظبط |
| `cd_or_td_currency` / `td_currency` | عملة الضمان — بتتستخدم كعملة التغطية لو `lc_cash_cover_currency` فاضي |

---

## ٥. الكشوف (Statements) اللي بتتكتب

النظام بيكتب في **٤ جداول** مختلفة. فهمهم هو مفتاح فهم الشاشة كلها.

### أ) `letter_of_credit_statements` — كشف الاعتمادات

ده اللي بيقولك **إنت مستهلك قد إيه من حد التسهيل**.

- عند الإصدار: صف `credit` بقيمة الاعتماد → الرصيد بيقل (استهلكت من الحد)
- عند الدفع: صف `for-paid` → الرصيد بيرجع (الاعتماد اتقفل)

الـ trigger بيحسب الرصيد تلقائياً:
```sql
set new.end_balance = new.beginning_balance + new.debit - new.credit ;
```

والسلسلة بتتقسّم بـ: `company_id + currency + financial_institution_id + source + lc_type` (وكمان `lc_facility_id` لو الـ source هو `lc-facility`).

> يعني كل بنك × كل عملة × كل نوع اعتماد = **سلسلة رصيد منفصلة**.

### ب) `letter_of_credit_cash_cover_statements` — كشف التغطيات

بيتتبّع **فلوسك المجمّدة** عند البنك. بيزيد عند الإصدار وبيقل عند الدفع.

### ج) `current_account_bank_statements` — الحساب الجاري

الفلوس اللي **خرجت من حسابك فعلاً**. بيتكتب فيه:
- التغطية النقدية (لو مش رصيد افتتاحي)
- مصاريف الإصدار
- العمولة
- الفوايد (وقت الدفع)
- قيمة الدفع (لو **انت** اللي موّلت)
- أي مصاريف إضافية بتضيفها

### د) `lc_overdraft_bank_statements` — مديونية البنك

بيتكتب **بس** لو البنك هو اللي موّل. ده بيمثّل: البنك دفع للمورّد وبقى ليه فلوس عندك، وعليها فوايد.

> **ملاحظة:** الـ trigger بتاع الجدول ده هو نفس trigger الأوفردرافت — فيه حسبة فوايد يومية وعمولة Highest Debt Balance آخر الشهر.

---

## ٦. الـ Popups والأزرار

من صفحة `letter-of-credit-issuance`، كل صف عليه الأزرار دي:

| الزرار | الـ popup | بيعمل إيه |
|---|---|---|
| 🖊️ **Edit** | صفحة كاملة | يفتح الفورم — بس افتكر إنه **حذف + إنشاء** |
| 💵 **Expenses** | `actions.blade.php` | يضيف/يعدّل/يمسح مصاريف إضافية (اسم + تاريخ + مبلغ + حساب) |
| ✅ **Mark as Paid** | `cancel-issuance-modal.blade.php` | يقفل الاعتماد — أهم popup |
| ↩️ **Back to Running** | `actions.blade.php` | يتراجع عن الدفع |
| 🗑️ **Delete** | modal تأكيد | يمسح الاعتماد وكل حركاته |

### حقول popup الدفع (`Mark as Paid`)

| الحقل | فايدته |
|---|---|
| `payment_date` | تاريخ الدفع — كل حركات الدفع بتتسجّل بيه |
| `supplier_invoice_id` | فاتورة المورّد — لو اتحددت بيتعمل **تسوية** (settlement) للفاتورة |
| `lc_remaining_amount` | المبلغ المتبقي للدفع |
| `payment_currency` / `payment_account_type_id` / `payment_account_number_id` | من أنهي حساب — **بتتجاهل تماماً لو التمويل من البنك** |
| `interest_amount` / `interest_currency` | فوايد التمويل — بتتخصم من الحساب الجاري |
| `allocations[]` | توزيع المبلغ على بنود/تعاقدات |

---

## ٧. مثال واقعي كامل

### السيناريو

شركة **الأمل للصناعات** عايزة تستورد ماكينة من ألمانيا.

| البند | القيمة |
|---|---|
| قيمة الماكينة | 100,000 EUR |
| البنك | البنك الأهلي |
| نوع الاعتماد | Sight LC |
| تاريخ الإصدار | 2026-01-15 |
| المدة | 90 يوم |
| سعر الصرف | 52.00 |
| نسبة التغطية | 20% |
| نسبة العمولة | 0.5% |
| الحد الأدنى للعمولة | 1,000 EGP |
| مصاريف الإصدار | 3,000 EGP |
| التمويل | من البنك |

### الخطوة ١: الإدخال

```
Source: lc-facility
Category: new-issuance
Transaction Name: ماكينة تعبئة ألمانيا 2026
LC Type: sight_lc
LC Amount: 100,000    LC Currency: EUR    Exchange Rate: 52
Cash Cover Rate: 20%
Issuance Date: 2026-01-15    LC Duration: 90 → Due Date: 2026-04-15
Financed By: Bank
```

### الخطوة ٢: النظام بيحسب

```
amount_in_main_currency = 100,000 × 52       = 5,200,000 EGP
cash_cover_amount       = 5,200,000 × 20%    = 1,040,000 EGP
lc_commission_amount    = 5,200,000 × 0.5%   =    26,000 EGP
maxLcCommissionAmount   = max(1,000 , 26,000) =    26,000 EGP
```

### الخطوة ٣: الحركات اللي بتتكتب (٦ صفوف)

| # | الجدول | مدين | دائن | التعليق |
|---|---|---|---|---|
| 1 | `current_account_bank_statements` | — | 1,040,000 | Cash Cover [ألمانيا] |
| 2 | `current_account_bank_statements` | — | 3,000 | Issuance Fees [ألمانيا] |
| 3 | `letter_of_credit_statements` | — | 5,200,000 | LC Issuance |
| 4 | `letter_of_credit_statements` | — | 1,040,000 | LC Issuance Cash Cover |
| 5 | `letter_of_credit_cash_cover_statements` | — | 1,040,000 | — |
| 6 | `current_account_bank_statements` | — | 26,000 | Commission Fees |

**النتيجة:** خرج من حسابك الجاري **1,069,000 جنيه** (تغطية + مصاريف + عمولة)، واستهلكت **5,200,000** من حد التسهيل.

### الخطوة ٤: بعد 90 يوم — المستندات وصلت

بتدوس **Mark as Paid**:

```
Payment Date: 2026-04-15
Supplier Invoice: INV-2026-0042
Interest Amount: 85,000 EGP
```

النظام بيحسب:
```
diffBetweenLcAmountAndCashCover = 5,200,000 − 1,040,000 = 4,160,000 EGP
```

| # | الجدول | الحركة | القيمة |
|---|---|---|---|
| 1 | `letter_of_credit_statements` | قفل الاعتماد (`for-paid`) | 5,200,000 |
| 2 | `letter_of_credit_cash_cover_statements` | فكّ التغطية (مدين) | 1,040,000 |
| 3 | `current_account_bank_statements` | فوايد | 85,000 |
| 4 | **`lc_overdraft_bank_statements`** | **مديونية للبنك** | **4,160,000** |
| 5 | `payment_settlements` | تسوية فاتورة INV-2026-0042 | |

**لو كان `Financed By: Self`** بدل Bank → الصف رقم 4 كان هيتكتب في **الحساب الجاري** بدل الأوفردرافت، والفلوس كانت هتخرج من حسابك على طول.

---

## ٨. المشاكل اللي لقيتها

> كل مشكلة هنا **متأكد منها من الكود** ومعظمها متأكد منها كمان من الداتا الموجودة في قاعدة البيانات.

### 🔴 ١. خطأ إملائي في `update()` بيمسح أمر الشراء كل مرة

**المكان:** [`LetterOfCreditIssuanceController.php:291`](app/Http/Controllers/LetterOfCreditIssuanceController.php#L291)

```php
if($letterOfCreditIssuance->getContractType() == 'no-po' && $request->get('contract-id') != -1){
    $letterOfCreditIssuance->purchaseOrder->delete();
}
```

اسم الحقل في الفورم `contract_id` بـ **underscore**، والكود بيقرا `contract-id` بـ **شرطة**. فالقيمة **دايماً `null`**.

**النتيجة:**
- `null != -1` → **صح دايماً** → أمر الشراء بيتمسح في **كل** تعديل
- والشرط اللي بعده (`== -1`) → **غلط دايماً** → رقم الـ PO عمره ما بيتحدّث

**ليه ده خطير:** بعد ما الـ PO يتمسح، `storeWithinTransaction` بيعمل واحد جديد بنفس الرقم — بس بـ **id جديد**. وأي `po_allocations` كانت مربوطة بالـ id القديم بتبقى يتيمة. (فيه 12 صف `po_allocations` في النظام دلوقتي).

**الإصلاح:** `$request->get('contract_id')`

---

### 🔴 ٢. مقارنة رقم حساب برقم نوع حساب

**المكان:** [`LetterOfCreditIssuanceController.php:251`](app/Http/Controllers/LetterOfCreditIssuanceController.php#L251)

```php
$isCdOrTdCashCoverAccount = in_array($request->get('cash_cover_deducted_from_account_id',[]),[28,29]);
```

الرقمين 28 و 29 دول **ids بتاعت `account_types`**:

| id | النوع |
|---|---|
| 28 | Time Of Deposit (T/D) |
| 29 | Certificate Of Deposit (C/D) |

لكن `cash_cover_deducted_from_account_id` بيحمل **id بتاع `financial_institution_accounts`** — حاجة تانية خالص. الحقل الصح هو `cash_cover_deducted_from_account_type`.

**اتأكدت من الداتا:** مفيش أصلاً حسابات بـ id = 28 أو 29 في `financial_institution_accounts` → **الشرط دايماً `false`**.

**النتيجة:** الحماية المفروض تمنع خصم التغطية من الحساب الجاري لما التغطية جاية من شهادة/وديعة — **معطّلة تماماً**. يعني في `against-cd` و `against-td` النظام بيخصم التغطية من الحساب الجاري **وكمان** بيرهن الشهادة → **الفلوس محسوبة مرتين**.

بالإضافة لكده `$request->get('...', [])` بيدي مصفوفة كقيمة افتراضية لـ `in_array` اللي بياخد قيمة مفردة — كود مش منطقي حتى لو الأرقام كانت صح.

---

### 🔴 ٣. المصاريف الإضافية بتضيع عند التعديل

`deleteAllRelations()` بيمسح كل الحركات **ماعدا `lc_issuance_expenses`**. وعشان التعديل = حذف + إنشاء، الاعتماد بياخد **id جديد** والمصاريف بتفضل مربوطة بالـ id القديم اللي اتمسح.

**اتأكدت من الداتا:**

```
foreign keys على lc_issuance_expenses: 0
مصاريف يتيمة (اعتمادها اتمسح): 1 من إجمالي 2
```

**نص المصاريف الموجودة في النظام دلوقتي يتيمة فعلاً.** ومفيش FK في الداتابيز يمنع ده.

**الإصلاح:** إضافة `$this->expenses()->delete()` في `deleteAllRelations()` — أو الأفضل، تغيير طريقة التعديل عشان ما تمسحش وتعيد الإنشاء.

---

### 🟠 ٤. الفاليديشن شبه معدوم

[`StoreLetterOfCreditIssuanceRequest`](app/Http/Requests/StoreLetterOfCreditIssuanceRequest.php) فيه **قاعدة واحدة بس**:

```php
return ['lc_amount' => ['required','gt:0']];
```

يعني **مفيش أي فحص** على: `issuance_date` · `lc_currency` · `exchange_rate` · `cash_cover_rate` · `lc_fees_and_commission_account_id` · `financial_institution_id` · `lc_type`.

**نتيجة عملية:** لو `lc_fees_and_commission_account_id` مبعتش أو غلط:

```php
$financialInstitutionAccountForFeesAndCommission = FinancialInstitutionAccount::find($lcFeesAndCommissionAccountId);
$financialInstitutionAccountIdForFeesAndCommission = $financialInstitutionAccountForFeesAndCommission->id;  // 💥
```

→ `Call to a member function id on null` — صفحة 500 بدل رسالة مفهومة.

---

### 🟠 ٥. احتمال انهيار لو عملة التغطية فاضية

**المكان:** [`LetterOfCreditIssuanceController.php:235`](app/Http/Controllers/LetterOfCreditIssuanceController.php#L235)

```php
$lcCashCoverOrCdOrTdCurrency = $model->getLcCashCoverCurrency() ?: $cdOrTdAccount->getCurrency();
```

في `lc-facility` و `hundred-percentage-cash-cover` مفيش شهادة/وديعة → `$cdOrTdAccount` بيفضل `null`. فلو `lc_cash_cover_currency` فاضية → **انهيار**.

**اتأكدت من الداتا:** من 3 اعتمادات `lc-facility` موجودة، **2 منهم عملة التغطية بتاعتهم فاضية**. أي محاولة إعادة حفظ ليهم هتضرب.

---

### 🟠 ٦. العمولة الدورية معطّلة

**المكان:** [`LetterOfCreditIssuanceController.php:267-271`](app/Http/Controllers/LetterOfCreditIssuanceController.php#L267)

```php
// $lcDurationDays = $request->get('lc_duration_days',1);
// $numberOfIterationsForQuarter = ceil($lcDurationDays / 3);
$numberOfIterationsForQuarter = 1 ;
$lcCommissionInterval = 'monthly';
// $lcCommissionInterval = $request->get('lc_commission_interval','monthly');
```

الكود اللي بيقرا المدة والفترة **متعلّم كوميت**، والقيم متثبّتة. فـ `storeCommissionAmountCreditBankStatement` بيدخل على مسار `else` وبيسجّل **عمولة واحدة بس يوم الإصدار**.

**النتيجة:** حقل `LC Duration (Days)` بيأثر على `due_date` بس ومالوش أي تأثير على العمولة. وحقل `lc_commission_interval` موجود في فورم الـ `hundred-percentage-cash-cover` بس ومبيتقريش خالص.

**سؤال للعميل:** هل العمولة المفروض تتحسب مرة واحدة ولا كل فترة؟ لو دورية، ده نقص وظيفي مش مجرد كود ميت.

---

### 🟡 ٧. تعارض بين trigger الإدخال وtrigger التعديل

في [`letter_of_credit_statements.sql`](app/Triggers/Cashvero/letter_of_credit_statements.sql):

| | الإدخال (سطر 20) | التعديل (سطر 46) |
|---|---|---|
| | `if(_count_all_rows, _last_end_balance, ifnull(new.beginning_balance,0))` | `set new.beginning_balance = _last_end_balance ;` |

عند الإدخال: لو الصف ده **أول صف** في السلسلة، بيحافظ على `beginning_balance` اللي انت بعته.
عند التعديل: بيحطه = `_last_end_balance` **من غير أي شرط**، واللي بيبقى `0` لو مفيش صف قبله.

**النتيجة:** أول صف في أي سلسلة (زي صف الرصيد الافتتاحي) لو اتلمس تاني، **رصيده الافتتاحي بيتصفّر**.

---

### 🟡 ٨. اختلاف بين الدفع والتراجع عن الدفع

```php
// markAsPaid  (سطر 429)
->where('source',$source)->where('is_credit',1)

// backToRunning (سطر 355)
->where('source',$source)              // من غير is_credit
```

`markAsPaid` بيمسح صفوف الأوفردرافت الدائنة بس، و`backToRunning` بيمسحهم كلهم. الاتنين المفروض يشتغلوا على نفس المجموعة عشان الرجوع يكون معكوس بالظبط للدفع.

---

### 🟡 ٩. مفيش تكامل مع أودو

أول سطر في الكنترولر:

```php
/**
 * ! No Odoo Service Yet
 */
```

كل الشاشات المشابهة (المصاريف، التحصيلات، الدفعات) بتتزامن مع أودو. دورة الاعتماد المستندي **لأ** — رغم إنها بتعمل حركات على الحساب الجاري (تغطية + مصاريف + عمولة + فوايد) المفروض تظهر في القيود المحاسبية.

---

### 🟡 ١٠. مفيش فحص تكرار على `lc_code`

`lc_code` هو رقم الاعتماد من البنك — المفروض يكون فريد. مفيش أي فحص لا في الفاليديشن ولا في الداتابيز.

---

## ٩. حالة الإصلاح

> اتعمل في **2026-08-12**. كل إصلاح متأكد منه باختبار فعلي — **29 فحص كلهم نجحوا**.

| # | المشكلة | الحالة | الإصلاح |
|---|---|---|---|
| 1 | `contract-id` بدل `contract_id` | ✅ **اتصلح** | `LetterOfCreditIssuanceController::update()` |
| 2 | مقارنة id الحساب بـ id النوع | ✅ **اتصلح** | بقى يقرا `cash_cover_deducted_from_account_type` ويستخدم `isCertificateOfDeposit()` / `isTimeOfDeposit()` |
| 3 | المصاريف اليتيمة | ✅ **اتصلح** | `deleteAllRelations(bool $includeExpenses = true)` + إعادة ربط المصاريف في `update()` |
| 4 | الفاليديشن | ✅ **اتصلح** | من قاعدة واحدة لـ **٨ قواعد** |
| 5 | انهيار عملة التغطية | ✅ **اتصلح** | فحص null + رسالة مفهومة لحساب المصاريف |
| 6 | العمولة الدورية | ⏸️ **موقوف** | محتاج قرار — شوف تحت |
| 7 | تعارض الـ triggers | ✅ **اتصلح** | `letter_of_credit_statements.sql` — الـ update بقى زي الـ insert |
| 8 | اختلاف الدفع/التراجع | ✅ **اتصلح** | `backToRunning` بقى بنفس فلتر `is_credit = 1` |
| 9 | تكامل أودو | ⏸️ **مفتوح** | شغل كبير — محتاج تخطيط منفصل |
| 10 | تكرار `lc_code` | ✅ **اتصلح** | فاليديشن + unique index `(company_id, financial_institution_id, lc_code)` |

### نتايج الاختبار بالأرقام

**الـ trigger (مشكلة ٧)** — أدخلت صف أول في سلسلة جديدة برصيد افتتاحي 750,000 ولمسته:

```
بعد الإدخال : beginning = 750,000.00   end = 750,000.00
بعد اللمس   : beginning = 750,000.00   end = 750,000.00   ✅
```

قبل الإصلاح كان بيتصفّر لـ `0.00` عند أول لمسة.

**تكرار `lc_code` (مشكلة ١٠)** — الفهرس والفاليديشن الاتنين شغالين:

```
نفس الكود + نفس البنك (9)     → Duplicate entry           ✅ اترفض
نفس الكود + بنك مختلف (9→10)  → اتقبل                      ✅
الفاليديشن                     → "رقم الاعتماد ده مستخدم قبل كده مع نفس البنك"
```

**المصاريف (مشكلة ٣)**:

```
deleteAllRelations(false)  → المصروف فضل موجود   ✅ (حالة التعديل)
deleteAllRelations()       → المصروف اتمسح       ✅ (حالة الحذف)
```

**الفاليديشن (مشكلة ٤)** — طلب فاضي بقى بيترفض بـ ٨ أخطاء بدل ما كان بيعدي ويرمي 500.

---

### ⏸️ مشكلة ٦ — ليه مصلحتهاش

الكود المتعلّم كوميت **نفسه غلط**:

```php
$numberOfIterationsForQuarter = ceil($lcDurationDays / 3);
```

`lc_duration_days` بالأيام، وتقسيمه على 3 مش بيدي عدد أرباع السنة. اعتماد مدته **90 يوم** كان هيطلع `ceil(90/3) = 30` → **٣٠ صف عمولة** بدل 1 (لو ربع سنوي المفروض يطلع 1). الصيغة دي كانت مكتوبة على أساس إن المدة **بالشهور** مش بالأيام.

فتشغيل الكود ده زي ما هو هيحوّل مشكلة لمشكلة أكبر. محتاج إجابة من العميل على:

1. العمولة بتتحسب **مرة واحدة** عند الإصدار، ولا **دورية**؟
2. لو دورية — كل قد إيه؟ (شهري / ربع سنوي)
3. الوحدة اللي بتحدد عدد المرات إيه بالظبط؟

### ⏸️ باقي مفتوح

- **المصاريف اليتيمة القديمة**: فيه **صف واحد** في قاعدة البيانات لسه يتيم من قبل الإصلاح. الإصلاح بيمنع حصول ده تاني بس ما بينضّفش القديم — محتاج قرار: يتمسح ولا يترجع لاعتماد؟
- **تكامل أودو (مشكلة ٩)**: لسه مفيش. الشاشة بتعمل حركات على الحساب الجاري ما بتوصلش لأودو.

### للنشر

```bash
php artisan migrate      # فهرس lc_code
php artisan run:sql      # trigger letter_of_credit_statements
```
