# نقل تعديلات أودو من cashvero.evoqas.com إلى system.veroanalysisb.com

**التاريخ:** 13 أغسطس 2026
**المصدر:** `/media/salah/Software/projects/cashvero.evoqas.com`
**الهدف:** `/media/salah/Software/projects/system.veroanalysisb.com`

---

## أهم حاجة تعرفها الأول: النقل مكانش نسخ ملفات

المشروعان **اتفرّقوا في الاتجاهين**، مش فرع واحد سابق التاني:

| | cashvero | system.veroanalysisb |
|---|---|---|
| الواجهة | **Vue + Inertia** (`inertiajs/inertia-laravel` في composer، 40+ صفحة في `resources/js/Pages`) | **Blade** (مفيش Inertia خالص، `resources/js/Pages` فيها مجلد واحد) |
| ملفات أودو موجودة هنا بس | — | `OdooExpensesController`, `ReadOdooExpense`, `OdooExpense` model, `OdooProbeSoPoLinkCommand` |
| هيلبر تحليل الحسابات | `HVero::getAnalysisAccountIds()` | `HNonBanking::getAnalysisAccountIds()` |

يعني الكونترولرز في cashvero بترجع `Inertia::render(...)`، وهنا بترجع `view(...)` أو JSON للـ jQuery. **نسخ أي كونترولر كما هو كان هيكسر الصفحة.**

فاللي حصل فعلياً: قرأت الـ diff، فصلت **منطق أودو** (اللي مشترك) عن **طبقة العرض** (اللي مختلفة)، ونقلت المنطق وأعدت كتابة العرض بـ Blade.

**عشان كده جدول "المصدر" تحت مهم** — فيه حاجات منقولة حرفياً، وحاجات أعدت كتابتها، وحاجات **كتبتها من عندي** لأن نسخة cashvero مكانتش شغالة هنا.

---

## جدول النقل الكامل

المفتاح:
- 📋 **منقول حرفياً** — نفس الكود من cashvero
- 🔧 **منقول ومُكيَّف** — نفس المنطق، معاد كتابته لـ Blade
- ✍️ **من عندي** — مش موجود في cashvero، اضطريت أكتبه عشان النقل يشتغل صح هنا

### النقطة 1 — ExpensesController المرتبط بأودو

| التعديل | الملف | المصدر | متأكد إنه شغال؟ |
|---|---|---|---|
| لف mark-as-paid في `DB::transaction` | `app/Http/Controllers/CashExpenseController.php` | 📋 | ⚠️ **لأ** — مفيش داتا (النسخة التوأم في MoneyPayment ✅ اتأكدت على staging) |
| تقييد `markPayableChequeAsPaidInOdoo()` بـ `hasOdooIntegrationCredentials()` | نفس الملف | 📋 | ⚠️ نفس الكلام |
| `hasOdooError()` + `getOdooError()` | `app/Models/CashExpense.php` | 📋 | ✅ **أيوه** |

> **الحاجات اللي مش في cashvero وسيبتها:** الـ `index()` و`create()` و`edit()` في cashvero اتحولوا لـ Vue بالكامل، ومعاهم فاليديشن سعر الصرف (`exchange_rate <= 0` → قسمة على صفر). ده **مش منقول** لأنه مش من نقاطك الستة ومش مرتبط بأودو.

### النقطتان 2 و 5 — باج mark as paid

| التعديل | الملف | المصدر | متأكد؟ |
|---|---|---|---|
| `DB::transaction` حوالين (تحديث محلي + أودو + تصحيح تاريخ الحركة) | `app/Http/Controllers/MoneyPaymentController.php` | 📋 | ✅ **أيوه — بنداء أودو حقيقي على staging** |
| `catch` بيرجّع رسالة الخطأ بدل ما يعدّي بصمت | نفس الملف | 🔧 (cashvero بيرجع redirect، هنا فيه فرع `ajax()` كمان) | ✅ **أيوه — على staging** |
| رسالة نجاح على الـ redirect | `MoneyPaymentController` + `CashExpenseController` | 📋 | ✅ |

**الباج كان:** لو أودو فشلت، الشيك بيتعلم عليه "مدفوع" محلياً وأودو مسجلتش حاجة، ومفيش أي حاجة على الشاشة توضح إن الاتنين اختلفوا.

### النقطة 3 — أزرار حالة أودو في باقي الصفحات

| التعديل | الملف | المصدر | متأكد؟ |
|---|---|---|---|
| `getOdooError()` لـ BuyOrSellCurrency / InternalMoneyTransfer / TimeOfDeposit | `app/Models/*.php` | 📋 (TimeOfDeposit ✍️ — cashvero نفسه مفيهوش) | ✅ |
| `hasOdooError()` + `getOdooError()` لـ LetterOfGuaranteeIssuance | `app/Models/LetterOfGuaranteeIssuance.php` | 📋 | ✅ |
| إضافة `_user_odoo_modal` لـ 5 صفحات | 5 ملفات blade | 🔧 | ✅ |
| **جعل زرار Resend اختياري** | `resources/views/reports/_user_odoo_modal.blade.php` | ✍️ | ✅ |

> ⚠️ **باج حقيقي لقيته أثناء الشغل (مش من نقاطك):**
> `_user_odoo_modal` كان بيبني الفورم دايماً على
> `route('resend.with.odoo', ['moneyReceived' => $model->id])`،
> والراوت ده مربوط `MoneyReceivedController::resendToOdoo(MoneyReceived $moneyReceived)`.
> يعني الضغط على **Resend** من صفحة **money payments** كان بياخد `id` الدفعة ويدوّر بيه في جدول `money_received` — فيعيد إرسال **سجل تاني خالص** بنفس الرقم، أو يطلع 404.
> cashvero **عارف المشكلة دي وسايبها** (مكتوبة في كومنت في `MoneyPaymentController` عندهم) — هما ببساطة مبيعرضوش زرار Resend في صفحاتهم الجديدة.
> أنا خليت الزرار يظهر بس لما الصفحة تبعت `resendUrl` حقيقي. النتيجة: Resend على money received بس، وباقي الصفحات بتعرض الخطأ للقراءة.

### النقطة 4 — أودو down payment settlement

| التعديل | الملف | المصدر | متأكد؟ |
|---|---|---|---|
| ميجريشن `synced_with_odoo` + `odoo_error_message` للجدولين | `database/migrations/2026_08_13_090000_*.php` | 📋 | ✅ **اتنفذت فعلاً** (batch 291) |
| `hasOdooError()` + `getOdooError()` | `app/Models/Settlement.php`, `PaymentSettlement.php` | 📋 | ✅ |
| قراءة الـ reference بعد `action_post` | `app/Services/Api/OdooPayment.php` | 📋 | ✅ **أيوه — حركة حقيقية على staging رجّعت `TA/2026/08/0003`** |
| تسجيل نجاح/فشل كل فاتورة على صف التسوية | نفس الملف | 📋 | ✅ **أيوه — بخطأ أودو حقيقي على staging** |
| `success` بيعكس الواقع بدل `true` دايماً | نفس الملف | 📋 | ✅ **أيوه — نجاح وفشل حقيقيين على staging** |
| عرض العلامتين في صفحة الدفعات المقدمة | `DownPaymentContractsController` + blade | 🔧 | ✅ |

**الباج الأصلي:** `settleAdvanceWithInvoices()` كانت بترجع `'success' => true` **دايماً**، مهما فشل. فلو كل الفواتير فشلت مع أودو، الكونترولر يشوف `true` ويقول للمستخدم **"Data Store Successfully"**.

### النقطة 6 — سرعة الداشبوردات

| التعديل | الملف | المصدر | متأكد؟ |
|---|---|---|---|
| حذف `getAllUniquePartnerIds` + `getAllUniquePartnerIdsForCheques` | `CustomerInvoiceDashboardController` | 📋 | ✅ |
| eager-load في ChequeAgingService (N+1) | `app/ReadyFunctions/ChequeAgingService.php` | 📋 | ✅ |
| شرط `!$invoiceDueDate` في InvoiceAgingService | `app/ReadyFunctions/InvoiceAgingService.php` | 📋 | ✅ |
| `keyBy('id')` بدل `FinancialInstitution::find()` جوه اللوب | `CustomerInvoiceDashboardController` | 📋 | ✅ |
| حساب عملة واحدة بس + تابات كلينكات | كونترولر + blade × 2 | ✍️ (cashvero عامله بـ Vue pills) | ✅ |
| لفة رخيصة تحسب `canShowDashboardPerCurrency` لكل العملات | `CustomerInvoiceDashboardController` | ✍️ | ✅ |
| رسم التابات من `$allCurrencies` بدل `$selectedCurrencies` | blade × 2 | ✍️ | ✅ |

> ❌ **حاجة في cashvero رفضت أنقلها عمداً:**
> عندهم `$request->ajax()` اتغيرت لـ `$request->filled()` في `viewLGLCDashboard`.
> ده فيكس **خاص بـ Inertia** (Inertia بيبعت نفس هيدر jQuery فالشرط بقى دايماً true عندهم).
> **هنا الصفحة لسه بتستخدم jQuery ajax حقيقي** — لو نقلته كنت هكسر التحديث الجزئي للشارتس.

### تعديلات إضافية (اللي وافقت عليها)

| التعديل | الملف | متأكد؟ |
|---|---|---|
| فيكس Carbon 3 (المدة بالسالب) — موضعين | `app/Services/Api/OdooService.php` | ✅ **أيوه — على أودو الحقيقي** |
| `syncBranchSafe` null guard | نفس الملف | ✅ **أيوه — على أودو الحقيقي** |
| `getJournalIdFromChartOfAccountId(?int)` | `app/Services/Api/Traits/CommonHelper.php` | ✅ **أيوه** |
| `PaymentSettlement::moneyPayment()` كانت بترجع `MoneyReceived` | `app/Models/PaymentSettlement.php` | ✅ (بالـ reflection) |

**اللي رفضته حسب اختيارك:** `session()->put` → `session()->flash` (حوالي 9 مواضع).

---

## الاختبارات اللي عملتها فعلاً

كلها على قاعدة البيانات الحقيقية `veroanalysis`، شركة 92 (iTechs SI EG).

### 1. فحص نحوي — ✅ نجح
`php -l` على كل ملف PHP اتغير → صفر أخطاء.
`php -l` على كل الـ 902 blade المُترجَمة → فشل **2 بس**، الاتنين ملفات **مش أنا اللي لمستها** (`admin/ready-made-forms/expense.blade.php` و `notifications/index.blade.php`) — أخطاء موجودة قبل شغلي، أثبتها بـ `git diff --stat` عليهم (فاضي).

### 2. رندر الصفحات — ✅ نجح
نداء الكونترولرز مباشرةً بـ request حقيقي (route resolver + `$company` + error bag):

```
forecast (default, lazy)         OK   514ms   82 queries    989,017 bytes
forecast (?currencies[]=USD)     OK   296ms   73 queries    983,853 bytes
lglc (default, lazy)             OK   291ms  323 queries    648,729 bytes
lglc (?currencies[]=USD)         OK   277ms  307 queries    648,729 bytes
cash expenses index              OK   290ms   29 queries    982,872 bytes
money payments index             OK   492ms   76 queries  2,686,834 bytes
money received index             OK   319ms   58 queries  1,067,204 bytes
internal money transfer index    OK   341ms  270 queries  1,005,469 bytes
buy or sell currency index       OK   191ms   30 queries    737,474 bytes
LG issuance index                OK   285ms   30 queries    951,702 bytes
down payment contracts           OK          591,588 bytes
time of deposit index            OK          663,010 bytes
```

### 3. قياس السرعة (قبل/بعد بـ `git stash`) — ✅ نجح

| الصفحة | قبل | بعد |
|---|---|---|
| forecast (افتراضي) | 633ms / **179 استعلام** / 2.77 MB | 514ms / **82 استعلام** / 0.99 MB |
| lglc (افتراضي) | 300ms / 366 استعلام | 291ms / 323 استعلام |

> **بصراحة:** مكسب lglc على الشركة دي **ضعيف** (366 → 323) لأن عندها عملة واحدة بس فيها بيانات LG/LC، فمفيش عملات زيادة نوفرها. المكسب الحقيقي هيبان على شركة بعملات كتير.
>
> وكمان: `lglc?currencies[]=USD` بقى **307 استعلام بعد** مقابل **54 قبل** — زيادة مقصودة، لأني ضفت لفة رخيصة بتحسب حد التسهيل لكل العملات عشان شريط التابات يفضل كامل. المكسب في الحالة الافتراضية (اللي بتحصل كل مرة)، مش في الحالة دي.

### 4. أزرار أودو — ✅ نجح
حطيت خطأ أودو على كل صفوف الشركة **جوه transaction اتعمله rollback**:

| الصفحة | زرار الخطأ 🐞 | رسالة الخطأ | زرار Resend |
|---|---|---|---|
| cash expenses | 40 | 40 | 0 |
| internal money transfer | 53 | 53 | 0 |
| buy or sell currency | 17 | 17 | 0 |
| LG issuance | 45 | 45 | 0 |
| money payments | 60 | 60 | 0 |
| money received | 45 | 45 | **45** |

بالظبط زي المطلوب: الخطأ في كل الصفحات، والـ Resend في money received بس.

### 5. صفحة الدفعات المقدمة — ✅ نجح
- تسوية **فاشلة** → 🐞 ظهر، ورسالة الخطأ اتعرضت
- تسوية **ناجحة** → 👍 ظهر، والمرجع اتعرض كـ `REF — Transfer Customer Advance to Receivable`

### 6. رولباك mark as paid — ✅ نجح
على شيك حالته `pending` فعلاً:

```
أودو بترمي استثناء   → قبل='pending'  بعد='pending'  ROLLED BACK (صح)
أودو بتنجح           → قبل='pending'  بعد='paid'     PERSISTED (صح)
```

### 7. نظافة القاعدة — ✅ نجح
كل بيانات الاختبار اتعملها rollback. اتأكدت **بـ SQL مباشر** مش بالكود:
`total_test_leftovers = 0`, `paid_2026_08_13_leftovers = 0`, وبيانات أودو بتاعة الشركة والمستخدم رجعت لقيمتها الأصلية.

---

## اختبار على سيرفر أودو الحقيقي (read-only)

اتعمل بعد كده بطلبك: **ببيانات أودو بتاعة اليوزر رقم 1، من غير أي كتابة في أودو.**

```
Odoo server : https://squadbcc-itechs-may2025v2-main-20015687.dev.odoo.com
Odoo db     : squadbcc-itechs-may2025v2-main-20015687
Odoo user   : mohamed.elraffie@squadbcc.com
server version : 18.0+e     authenticated uid = 6
```

### الضمانة إن مفيش كتابة
استبدلت `$this->models` (كلاينت XML-RPC) بـ **حارس** بيرمي استثناء على أي method مش قراءة.
المسموح بس: `search`, `read`, `search_read`, `search_count`, `fields_get`, `name_search`, `name_get`, `default_get`.
سجل النداءات الفعلي:

```
account.account::search   x1     project.project::search   x1     account.move::search   x2
account.account::read     x1     project.project::read     x1     account.move::read     x2
✅ zero write attempts — كل نداء كان search/read
```

### النتائج

| # | الاختبار | النتيجة |
|---|---|---|
| 1 | مصادقة ببيانات اليوزر 1 | ✅ نجحت، uid = 6، أودو 18.0+e |
| 2 | `syncBranchSafe()` null guard | ✅ رجعت عادي بدل ما تطلع fatal |
| 3 | فيكس Carbon 3 على مشاريع حقيقية | ✅ **13 من 15 مشروع كانوا بيدوا مدة بالسالب** |
| 4 | قراءة اسم `account.move` بعد الـ post | ✅ رجّع `"PCUST/2026/00359"` |

**اختبار 3 — أهم نتيجة.** الباج كان حقيقي وواسع على بيانات الإنتاج:

```
project #55  Cisco Router                  2025-06-10 -> 2025-07-16  | قبل=-1.19    بعد=1.19
project #58  FABmisr                       2025-09-01 -> 2025-11-14  | قبل=-2.43    بعد=2.43
project #72  EGIT-EGXHQ-Smart Village DC   2026-08-06 -> 2026-10-28  | قبل=-2.71    بعد=2.71
project #69  M2 Maintenance Fire & shutter 2026-08-01 -> 2027-07-31  | قبل=-11.97   بعد=11.97
project #66  EGIT AI Cluster               2026-06-03 -> 2026-07-17  | قبل=-1.45    بعد=1.45
```

**اختبار 4 —** الحركة المرحّلة رجعت `"PCUST/2026/00359"`، والحركة الـ **draft** رجعت `false`.
ده بيثبت **ليه** لازم القراءة تحصل **بعد** `action_post` — وهو بالظبط اللي الكود الجديد بيعمله.

### 🐞 باج في **الفيكس بتاعي أنا** ظهر هنا واتصلح

اختبار 2 **فشل أول مرة**:

```
TypeError: getJournalIdFromChartOfAccountId(): Argument #1 ($chartOfAccountId)
           must be of type int, null given
```

الـ null guard اللي حطيته حوّل `$chartOfAccountId` لـ `null`، والسطر اللي بعده على طول بيبعتها لدالة نوعها `int`.
يعني **الفيكس بتاعي كان بيحوّل fatal error لـ fatal error تاني**.

السبب: cashvero عامل الفيكس على **جزئين** — واحد في `OdooService.php` وواحد في `Traits/CommonHelper.php`.
أنا كنت عامل diff لـ `OdooService.php` بس، فمشوفتش النص التاني.

الحل: `getJournalIdFromChartOfAccountId(?int $chartOfAccountId)` + `return null` بدري لو null
(ضفت الـ early return لأن الاستعلام بـ `default_account_id = null` بيرجع يوميات ملهاش حساب افتراضي وياخد **أول واحدة** — يعني يومية غلط).

**ودي علّمتني أعمل diff للمجلد كله**، فلقيت حاجتين تانيين مكنتش شايفهم:

| الملف | الفرق | القرار |
|---|---|---|
| `Traits/CommonHelper.php` | `int` → `?int` | ✅ **نُقل** (لازم للفيكس) |
| `Traits/AuthTrait.php` | `url/db` nullable + رسالة واضحة لو ناقصين | ⏸️ **مش منقول** — قولي لو عايزه |
| `Traits/HasJournalEntry.php` | `HNonBanking` → `HVero` | ❌ **متعمد مانقلتوش** — هنا اسمه `HNonBanking` |

---

## اختبار منطق تسوية الدفعة المقدمة (من غير شبكة خالص)

`settleAdvanceWithInvoices()` بتكتب في أودو، فمينفعش تتشغّل على الإنتاج.
بدل كده بنيت الأوبچكت بـ `newInstanceWithoutConstructor()` — **مبيتصلش بأودو أصلاً** — وعملت stub لكل دالة بتلمس أودو، فاللي اشتغل هو **منطقي أنا بس**.

**22 تأكيد، كلهم نجحوا:**

| السيناريو | النتيجة |
|---|---|
| **أ** — كل الفواتير فشلت في إنشاء الحركة | `success=false`، الرسالة "failed for all"، **الصفّين اتسجل عليهم سبب الفشل** |
| **ب** — الحركة نجحت والمطابقة فشلت | `success=false`، مش synced، بس **المرجع الحقيقي اتحفظ** (`TA/2026/08/0007`) + `odoo_move_id` |
| **ج** — جزئي: فاتورة نجحت وفاتورة فشلت | `success=false`، الرسالة **"partially failed (1 of 2 invoice(s) failed)"**، الصف الناجح synced بمرجعه والفاشل بسببه |
| **د** — كله نجح | `success=true`، "Advance settlement completed"، `total_settled=300`، الصفّين synced بمراجعهم |
| **هـ** — `hasOdooError()` / `getOdooError()` | صح في الحالتين |

السيناريو **د** هو الأهم: بيثبت إن الفيكس **مكسرش مسار النجاح**.
والسيناريو **أ** بيثبت الباج الأصلي: قبل الفيكس كانت هترجع `true` وتقول "Data Store Successfully".

---

## اختبار الكتابة الفعلية على **staging** — ✅ اتعمل

بعد ما وجّهت شركة 92 على `...stage1-35966572` بدل `...main-20015687`، جرّبت مسارات الكتابة كلها.

### 1. تسوية الدفعة المقدمة — end-to-end حقيقي

داتا حقيقية من staging: العميل **Smart Tech** (`partner 592`)، دفعة مقدمة `move 27656` (5,000,000 ج.م)، فاتورة مفتوحة `27555` = `INV/2026/00005`.

| السيناريو | النتيجة |
|---|---|
| **نجاح** — تسوية 100 ج.م على الفاتورة الحقيقية | `success=true` — واتعملت حركة حقيقية `move 28719` ورجع المرجع **`TA/2026/08/0003`** |
| **فشل** — تسوية على فاتورة مش موجودة | `success=false`، الخطأ الحقيقي من أودو **`"No invoice lines found"`** اتسجل علي صف التسوية، والمرجع `TA/2026/08/0004` **فضل محفوظ** |
| رسالة المستخدم في حالة الفشل | `"Odoo settlement failed: ..."` — **مش** "Data Store Successfully" |

> ✅ **دي أهم نقطة اتأكدت:** المرجع اللي بيترجع من أودو بعد الـ post (`TA/2026/08/0003`) **حقيقي ومحفوظ** — يعني التعديل بتاع `createJournalEntryTransfer` شغال فعلاً، مش بس نص القراءة.
>
> ✅ والباج الأصلي اتأكد إنه اتصلح: قبل الفيكس السيناريو التاني كان هيرجع `true` ويقول للمستخدم "تم الحفظ بنجاح".

### 2. mark as paid — الشيك مع أودو حقيقي

| السيناريو | النتيجة |
|---|---|
| **أودو شغال** | الشيك بقى `paid`، والمرجع اترجع **`QNB1/2026/00317 (Cheque Payment)`**، مفيش أخطاء |
| **أودو واقع** (وجّهت الـ URL على هوست مقفول) | الشيك **فضل `pending`** والمستخدم شاف `"Error While Connecting With Odoo : ... Connection refused"` |

> ✅ **ده بالظبط الفيكس بتاع النقطتين 2 و 5**، متأكد منه دلوقتي بنداء أودو حقيقي مش محاكاة.

### ⚠️ أول محاولة كانت **نجاح كاذب** — لازم تعرفها

أول ما جربت mark as paid على staging، الاختبارين "نجحوا"... **بس لسبب غلط**.
الرسالة كانت `"Net Balance Less Than Paid Amount"` — يعني الكونترولر خرج **بدري عند تشيك الرصيد**، وعمره ما وصل للترانزاكشن ولا لأودو أصلاً.
فكون الشيك فضل `pending` مكانش بيثبت أي حاجة عن الفيكس.

السبب: كل الـ 20 شيك غير المدفوع بتوع الشركة على حساب **مكشوف** (`-19,607.74`).
الحل: وجّهت الشيك على الحساب التاني الممول (`+320,393.18`) جوه الترانزاكشن اللي بيتعمله rollback — وساعتها الاختبار وصل للكود المطلوب فعلاً وطلع النتائج اللي فوق.

### النضافة بعد الاختبار
- حركات staging اللي اتعملت (`28719`, `28720`, `28721`) **اتمسحت كلها** — اتأكدت بـ search إنها مش موجودة.
- القاعدة المحلية اتعملها rollback: الشيكات رجعت `pending` على بنك 17، ورابط أودو زي ما هو.
- `users.odoo_id` كان بيتكاش بـ `6`؛ رجّعته `NULL` زي ما كان، لأنه هيبقى غلط لو رجّعت الشركة على الإنتاج.

---

## اللي **لسه** مش متأكد إنه شغال

### أ. `CashExpenseController::markChequesAsPaid()` — **متجربش ببيانات**
مفيش أي `CashExpense` مربوط بشيك **غير مدفوع** في القاعدة دي، فمقدرتش أثبت الرولباك عليه.
الكود مطابق لنسخة `MoneyPayment` اللي **اتجربت ونجحت**، بس ده استنتاج مش إثبات.

### ب. الجافاسكريبت في المتصفح — **متجربش**
اختباراتي كلها server-side. اللي محتاج عين بشرية:
- المودالز بتفتح فعلاً (bootstrap `data-toggle="modal"`)
- تابات العملات الجديدة (لينك مقابل تاب) شكلها مظبوط
- شارتس amCharts مبتضربش errors في الكونسول
- الرسالة بتظهر في Swal لما تسوية أودو تفشل

### ج. باقي الشركات الـ 26
كل حاجة اتجربت على شركة **92 بس**. شركة بعدد عملات مختلف أو من غير بيانات LG/LC ممكن تتصرف بشكل مختلف — أنا غطيت الحالة دي بـ fallback في الكود واختبار محاكاة، بس مش ببيانات حقيقية.

---

## باجات لقيتها **في شغلي أنا** وصلحتها قبل ما أسلّم

عشان تبقى عارف إن التست فعلاً نفع، مش مجرد إجراء شكلي:

1. **`view-period-interests.blade.php`** — ضفت `_user_odoo_modal` وبعدين اكتشفت إن `$row` هناك `CurrentAccountBankStatement` **ومفيهاش `hasOdooError()`** → كان هيعمل fatal error. تراجعت عن الإضافة دي.

2. **صفحة lglc بتطلع فاضية** — لما تطلب عملة ملهاش بيانات LG/LC، كان بيترسم بانل من غير تاب مقابل ولا حاجة `active` → صفحة بيضا ومفيش طريقة ترجع. صلحته بـ `array_intersect` مع العملات اللي عندها بيانات + fallback.

3. **المرجع بيتعرض مرتين** — `getOdooReferenceNames()` أصلاً بتلف على التسويات، فالمرجع كان بيظهر مرة مجرد ومرة موصوف. (نفس التكرار موجود في cashvero.) عملت dedupe.

4. **شريط التابات بيتقفل على عملة واحدة** — `?currencies[]=X` كان بيقلّص `$selectedCurrencies` فالتابات تختفي والمستخدم يتحبس. صلحته برسم التابات من `$allCurrencies`.

5. **الـ null guard بتاعي كان بيحوّل fatal لـ fatal تاني** — ظهر على أودو الحقيقي. كنت ناقل نص الفيكس بس؛ النص التاني في `Traits/CommonHelper.php`. (تفاصيله فوق.)

6. **اختبار mark-as-paid الأول كان نجاح كاذب** — كان بيخرج بدري عند تشيك الرصيد وعمره ما وصل لأودو. لو كنت اكتفيت بالـ ✅ كنت هبلغك إن حاجة اتأكدت وهي مااتأكدتش. (تفاصيله فوق.)

---

## اختلاف مقصود عن cashvero — قرارك

في `LetterOfGuaranteeIssuance::fullyIntegratedWithOdoo()`:

```php
// cashvero (المصدر):
return count($this->getOdooReferenceNames());

// اللي كتبته هنا:
return !$this->hasOdooError() && count($this->getOdooReferenceNames());
```

خليتها زي **باقي الموديلات كلها** (CashExpense, BuyOrSellCurrency, InternalMoneyTransfer, TimeOfDeposit) عشان مايظهرش 👍 و 🐞 مع بعض على نفس السجل.

**بس ده اختلاف عن المصدر.** لو عايزها مطابقة لـ cashvero بالظبط، شيل `!$this->hasOdooError() &&`.

---

## الملفات المتغيرة (28 ملف + 1 جديد)

```
app/Http/Controllers/CashExpenseController.php               |  48 +++--
app/Http/Controllers/CustomerInvoiceDashboardController.php  | 140 ++++++++---
app/Http/Controllers/DownPaymentContractsController.php      |  42 +++-
app/Http/Controllers/MoneyPaymentController.php              |  65 ++++--
app/Models/BuyOrSellCurrency.php                             |  11 +
app/Models/CashExpense.php                                   |  23 +-
app/Models/InternalMoneyTransfer.php                         |  11 +
app/Models/LetterOfGuaranteeIssuance.php                     |  17 +-
app/Models/PaymentSettlement.php                             |  24 +-
app/Models/Settlement.php                                    |  22 +-
app/Models/TimeOfDeposit.php                                 |  13 +-
app/ReadyFunctions/ChequeAgingService.php                    |   7 +-
app/ReadyFunctions/InvoiceAgingService.php                   |   7 +-
app/Services/Api/OdooPayment.php                             |  78 ++++++-
app/Services/Api/OdooService.php                             |  25 ++-
app/Services/Api/Traits/CommonHelper.php                     |  13 +-
resources/views/admin/dashboard/forecast.blade.php           |  61 ++++-
resources/views/admin/reports/lglc-report.blade.php          |  50 +++-
resources/views/buy-or-sell-currency/index.blade.php         |   4 +
resources/views/contracts-down-payment/index.blade.php       |  17 +-
resources/views/internal-money-transfer/index.blade.php      |   4 +
resources/views/reports/LetterOfGuaranteeIssuance/index...   |   4 +
resources/views/reports/_integrated_modal.blade.php          |  36 ++-
resources/views/reports/_user_odoo_modal.blade.php           |  38 ++-
resources/views/reports/cashExpenses/index.blade.php         |   3 +
resources/views/reports/moneyReceived/index.blade.php        |  14 +-
resources/views/reports/time-of-deposit/index.blade.php      |   3 +
resources/views/reports/time-of-deposit/view-period-int...   |   2 +
                                          28 ملف، +682 / -100

جديد: database/migrations/2026_08_13_090000_add_odoo_sync_status_to_settlements_tables.php
```

**الميجريشن اتنفذت محلياً بالفعل (batch 291). لسه محتاجة تتنفذ على الـ staging والـ production.**

---

## تغيير في السلوك لازم المستخدمين يعرفوه

في `/cashvero-dashboard/forecast` و `/cashvero-dashboard/lglc`:
الضغط على تاب عملة **مش محسوبة** بقى **بيعيد تحميل الصفحة** بدل التبديل الفوري.
ده تمن التحسين — قبل كده كل العملات كانت بتتحسب في كل مرة حتى لو محدش هيبص عليها.
العملة المفتوحة حالياً لسه تاب عادي بيتبدل فوراً.
