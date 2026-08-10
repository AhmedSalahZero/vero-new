# مراجعة فنية — Facility Renewal Design Brief

**التاريخ:** 2026-08-10
**الأساس:** فحص فعلي للكود وقاعدة البيانات (`veroanalysis`) — مش قراءة نظرية للوثيقة
**نطاق الفحص:** جداول ومحرّكات الـ Overdraft الأربعة، الـ triggers الخاصة بيها، ومسارات الحساب في PHP

---

## 0. الحكم النهائي في سطرين

الخطة **سليمة كوثيقة أعمال** — القواعد الأربعة صحيحة ومنطقية، ومبدأ «بعض الحاجات دايماً حالية وبعضها مقفول عند الإنشاء» هو التوصيف الصح.

لكنها **مبنية على قراءة غير دقيقة للنظام الحالي**: بتطلب بناء حاجات موجودة بالفعل، وبتقلّل خطورة حاجات تانية بدرجة كبيرة، وفيها قاعدتان (٣ و ٤) **مكسورتان في الكود النهارده** قبل ما نبدأ أي تجديد أصلاً.

---

## 1. تصحيحات واقعية للوثيقة

### 1.1 §5 — «Clean/Fully Secured: no history, build from scratch» ❌

جدول `clean_overdraft_rates` موجود من زمان:

```
id, company_id, clean_overdraft_id, date, borrowing_rate, margin_rate,
interest_rate, min_interest_rate, created_at, updated_at
```

والـ trigger بيعمل **بالظبط** اللي الخطة بتقترح بناءه من الصفر:

```sql
select min_interest_rate, interest_rate into _min_interest_rate, _interest_rate
from clean_overdraft_rates
where clean_overdraft_id = new.clean_overdraft_id and date <= new.date
order by date desc, id desc limit 1;
```

نفس الحاجة في `fully_secured_overdraft_rates` و `overdraft_against_commercial_paper_rates` و `overdraft_against_assignment_of_contract_rates`.

**الخلاصة:** «lookup السعر الساري في تاريخ معيّن» **شغّال بالفعل للأنواع الأربعة**.

اللي **فعلاً** مالوش history هو الحقول اللي على صف الـ facility نفسه:
`limit`، `highest_debt_balance_rate`، `admin_fees_rate`، `to_be_setteled_max_within_days`.

### 1.2 §5 — «ODA types: already has a dated history table, extend it to carry rate/fee terms» ❌

جداول `overdraft_against_*_limits` **مش** جداول terms:

```
id, is_active, company_id, overdraft_against_commercial_paper_id,
cheque_id, full_date, limit, accumulated_limit, created_at, updated_at
```

دي **صف لكل شيك/عقد** بيبني الـ limit تراكمياً، وعليها **٤ triggers** (before/after × insert/update) وفيها `is_active`. توسيعها بـ terms هيخلط بين بُعدين مختلفين تماماً ويكسر منطق التراكم.

### 1.3 §4 — «الـ account-number check محتاج تعديل» ⚠️ على الأرجح مش مشكلة

`UniqueAccountNumberRule` مستخدم في `Store*Request` **بس**:

```php
'account_number' => ['required', new UniqueAccountNumberRule($excludeAccountNumbers)],
```

لو التجديد = فصل مؤرَّخ على نفس السجل (زي ما الخطة بتقول)، فالقاعدة **مش هتتنادى خالص**. وكمان الـ rule أصلاً بياخد `$excludeAccountNumbers`، فالآلية موجودة لو احتجتها. **تأكد قبل ما تخصص شغل ليها.**

### 1.4 §6 — «Clean Overdraft الأول لأنه no auto-calculation involved» ❌

Clean Overdraft **فيه** حسابات تلقائية:
- فوائد لكل صف داخل الـ trigger (`interest_amount`, `days_count`, `interest_rate_daily`)
- وظيفة آخر الشهر `OverdraftEndOfMonthInterestCalculation` اللي بتحقن صفوف `highest_debit_balance` و `interest`

فترتيب «الأبسط» في §6 مبني على افتراض غلط.

---

## 2. الاصطدامات مع الـ Triggers — ٥ نقاط

> السياق: قاعدة البيانات فيها **أكتر من ٦٠ trigger**، منها ١٧ على جداول الـ overdraft وحدها.

### 2.1 🔴 الـ `limit` له **٣ سلوكيات مختلفة** عبر الأنواع الأربعة

ده أهم اكتشاف في التقرير. نفس المفهوم («حد التسهيل») متنفّذ بتلات طرق متعارضة:

| النوع | مصدر `new.limit` في الـ trigger | سلوك حد الـ facility |
|---|---|---|
| Clean Overdraft | `ifnull(new.limit, 0)` — من الصف نفسه | 🔒 **مجمَّد** لكل صف؛ جدول الأب **لا يُقرأ إطلاقاً** |
| Fully Secured | `new.limit` — من الصف نفسه | 🔒 **مجمَّد** لكل صف؛ نفس السلوك |
| ODA / Assignment of Contract | `LEAST(facility.limit, accumulated_limit@date)` | ♻️ **حالي دائماً** — يُقرأ حيّاً من الأب |
| ODA / Commercial Paper | `accumulated_limit@date` — **بدون أي سقف** | ⚠️ **غير مطبَّق إطلاقاً** |

الأكواد الفعلية:

```sql
-- Clean / Fully Secured (مجمَّد)
set new.limit = ifnull(new.limit, 0);
set new.room  = new.limit + new.end_balance;

-- Assignment of Contract (حالي دائماً — لاحظ الـ LEAST)
select accumulated_limit into _accumulated_limit from ..._limits
  where ... and date(full_date) <= date(new.full_date) order by full_date desc, id desc limit 1;
select `limit` into _limit from overdraft_against_assignment_of_contracts where id = ... limit 1;
set _accumulated_limit = least(_limit, _accumulated_limit);   -- ← السقف هنا
set new.limit = _accumulated_limit;

-- Commercial Paper (بدون سقف — لا LEAST)
select accumulated_limit into _accumulated_limit from ..._limits where ... limit 1;
set new.limit = _accumulated_limit;
```

مؤكَّد في الـ INSERT والـ UPDATE للأربعة.

**الأثر على الخطة — مختلف لكل نوع:**

- **Clean / FSO:** الفكرة المحورية (*"ask what was in force on this date"*) **مش هتشتغل**، لأن القيمة materialized في كل صف. أي تجديد للـ limit = إعادة كتابة `limit` + `room` على كل صف من تاريخ السريان. data migration مع كل تجديد.
- **Assignment of Contract:** التجديد **بيسري فوراً** — بس بأثر رجعي على **كل التاريخ**. تخفيض الحد من ٨٠م لـ ٥٠م هيعيد كتابة `limit`/`room` للصفوف التاريخية أول ما الـ cascade يلمسها. **ده كسر لمبدأ «الماضي لا يُعاد كتابته» بالنسبة للـ limit نفسه.**
- **Commercial Paper:** التجديد للـ limit **ملوش أي أثر على المحرّك** (راجع ٣.١).

**التوصية:** توحيد السلوك الثلاثي ده **قرار تصميمي لازم يتحسم قبل أي كود** — مينفعش تبني تجديد فوق ٣ دلالات مختلفة لنفس الرقم.

### 2.2 ⚠️ الأخطر — `to_be_setteled_max_within_days` بيتقرا **live** من الأب

```sql
select to_be_setteled_max_within_days into _clean_overdraft_to_be_settled_after
from clean_overdrafts where id = new.clean_overdraft_id;
```

ده جوه `refresh_calculation_before_update_clean_overdraft` — بالرغم إن `clean_overdraft_withdrawals` عنده أعمدة خاصة بيه:

```
max_settlement_days, due_date, settlement_amount, net_balance
```

**النتيجة: قاعدة رقم ٣ مكسورة النهارده.** أول ما حد يعدّل الرقم على الـ facility، أي إعادة لمس لأي صف قديم بتعيد حساب `due_date` بتاع سحب قديم بالقيمة الجديدة — وده بالظبط اللي الخطة بتوعد إنه **عمره ما يحصل**.

### 2.3 سلسلة الـ statements مترابطة وبتعدّل نفسها

كل صف بياخد `beginning_balance` من الصف اللي قبله، والـ update trigger بيعيد كتابة الـ `credit`:

```sql
set new.credit = _total_month_interest_amount + new.interest_amount;
```

والآلية المعتمدة لنشر أي تغيير هي **إعادة لمس كل صف بعديه** عشان الـ triggers تولّع تاني — عبر `StatementCascade::touchRows()` و `Model::updateNextRows()`، وموثّقة في `ProveLimitUpdateCascadeCommand`:

```php
// Fixed 2026-07-26: was a raw DB::table()->update() that moved the
// limit_update marker without firing BankStatement::updated →
// updateNextRows(). Neighbors that used the marker as their previous
// row kept stale days_count / interest_amount.
```

**الأثر:** تجديد بتاريخ رجعي = إعادة تشغيل triggers على كل ذيل السلسلة = إعادة حساب فوائد **وتواريخ استحقاق تاريخية** (مع 2.2). تخريب مؤكد.

### 2.4 🔴 قاعدة ٤ مكسورة كمان — الـ advance rate **مش** مقفول

`before_update_overdraft_against_commercial_paper_limits` **بيعيد اشتقاق** الـ limit من الجدول الحالي:

```sql
select lending_rate into _lending_rate from lending_information
where overdraft_against_commercial_paper_id = new.overdraft_against_commercial_paper_id
  and for_commercial_papers_due_within_days >= _days_count
order by for_commercial_papers_due_within_days asc limit 1;

set new.limit = LEAST(_lending_rate/100 * _cheque_amount, _max_lending_limit_per_customer);
```

يعني الشيك اللي اترصد بنسبة ٥٠٪ — أول ما صفّه يتحدّث لأي سبب (والـ cascade في 2.3 بيحدّثه كتير) — هياخد النسبة الجديدة ٨٠٪. **القفل عند تاريخ الإسناد مش موجود.**

### 2.5 🔴 `lending_information` **مفيهوش عمود تاريخ أصلاً**

```
id, overdraft_against_commercial_paper_id, lending_rate,
for_commercial_papers_due_within_days, company_id, created_at, updated_at
```

مفيش `date` ولا `effective_from`. **قاعدة ٤ غير قابلة للتنفيذ كما هي مكتوبة** من غير ما تضيف بُعد زمني للجدول ده الأول.

وكمان: الـ advance rate **مش رقم واحد** — ده **جدول شرائح** حسب أيام الاستحقاق (`for_commercial_papers_due_within_days`). فالوصف في الخطة *"(e.g. 50% → 80%)"* بيبسّط الموضوع أكتر من اللازم: التجديد بيغيّر **جدول شرائح كامل**، مش نسبة واحدة.

---

## 3. أخطاء قائمة اكتشفتها أثناء الفحص (مش جزء من الخطة)

### 3.1 🔴 حد الـ facility غير مطبَّق على Commercial Paper وحده

> **تصحيح:** نسخة أولى من التقرير قالت إن الحد «ملوش أي تأثير على النوعين التلقائيين». ده **غلط** — بعد فحص الـ ١٤ trigger كلها، الحد **مطبَّق فعلاً** على Assignment of Contract، لكن على مستوى الـ bank statement مش على مستوى جدول الـ limits. المشكلة الحقيقية أضيق وأدق: **عدم تماثل بين نوعين شقيقين**.

في جداول الـ `*_limits` الأربعة، `_max_limit` **بيتقرا وعمره ما بيتستخدم** — dead code في الأربعة:

```sql
declare _max_limit decimal(14,2) default 0;
select `limit`, max_lending_limit_per_customer into _max_limit, _max_lending_limit_per_customer
from overdraft_against_commercial_papers where id = ...;   -- _max_limit لا يُستخدم بعدها أبداً
```

لكن السقف الحقيقي بيتطبّق في الـ **bank statement** trigger — وهنا التفاوت:

```sql
-- Assignment of Contract ✅ السقف موجود
set _accumulated_limit = least(_limit, _accumulated_limit);

-- Commercial Paper ❌ السطر ده غير موجود
set new.limit = _accumulated_limit;
```

مؤكَّد في الـ INSERT والـ UPDATE للاتنين.

**الحالة الفعلية في البيانات (فحص مباشر):** مفيش أي تسهيل متجاوز حالياً — أعلى استغلال هو CP #10 بـ ‎11.9م من ‎35م (≈٣٤٪)، وصفوف كشوف الحساب المتجاوزة = **صفر** للنوعين. يعني ده **عيب كامن (latent)** مش خسارة قائمة.

**الأثر على الخطة:** تجديد الـ limit لـ Commercial Paper **ملوش أي أثر على المحرّك**، بينما نفس التجديد لـ Assignment of Contract بيسري فوراً وبأثر رجعي (راجع ٢.١). لازم يتحسم إذا كان حد الـ facility المفروض يقيّد التراكم أصلاً — **ده قرار عمل مش تقني**.

### 3.2 🔴 lookup الـ lending rate في Assignment of Contract مكسور

```sql
select lending_rate into _lending_rate from lending_information_against_assignment_of_contracts
where overdraft_against_assignment_of_contract_id = new.overdraft_against_assignment_of_contract_id
-- and for_assignment_of_contracts_due_within_days >= _days_count order by for_assignment_of_contracts_due_within_days asc
 limit 1;
```

الفلتر **متعلّق كوميت**، ومفيش `ORDER BY` — يعني `limit 1` بيرجع **صف عشوائي**. وبالرغم إن الجدول فيه `assignment_date` و `contract_id` و `customer_id`، الـ trigger **بيتجاهلهم كلهم**.

### 3.3 advance rate تالت غير مذكور في الخطة

`fully_secured_overdrafts.cd_or_td_lending_percentage` — نسبة إقراض مقابل الـ CD/TD، قيمة واحدة بدون تاريخ. الخطة بتتكلم عن الـ advance rate بتاع Commercial Paper بس.

### 3.4 وظيفة آخر الشهر بتقرا live

`OverdraftEndOfMonthInterestCalculation` بتقرا `limit` و `highest_debt_balance_rate` مباشرة من `clean_overdrafts`، وبتختم الـ `limit` الحالي في صف مؤرَّخ بالماضي:

```php
->select('company_id', 'limit', 'highest_debt_balance_rate')
...
'date' => $calculationDate,   // آخر يوم في شهر قديم
'limit' => $limit,            // القيمة الحالية!
```

لو اتشغّلت لشهر قديم بعد تجديد → limit غلط في صف تاريخي، وكمان بتحقن صف في نص السلسلة فتزحزح كل اللي بعده.

---

## 4. الإجابة على §3 (سلوك Assignment of Contract)

**الإجابة التجارية: أيوة، التماثل مع قاعدة ٤ هو القراءة الصحيحة** — والداتا موديل بيدعمه: `lending_information_against_assignment_of_contracts` فيه `assignment_date` و `contract_id` و `customer_id`.

**بس ده مش قابل للتنفيذ دلوقتي** بسبب 3.2 — الـ trigger مش بيحلّ السعر لا لكل عقد ولا لكل تاريخ. لازم يتصلّح الأول.

**نقطة إيجابية:** حدث «نهاية العقد» **معرّف بالفعل** في الـ trigger:

```sql
if(_contract_status = 'finished' and _number_of_contracts_existence > 1 and new.full_date = _max_full_date)
then set new.limit = new.limit * -1;
end if;
```

يعني لما العقد يخلص، بيتحط صف بقيمة سالبة يشيل مساهمته. فالسؤال «إمتى العقد يبطل يساهم؟» **متجاوب عليه في الكود** — مش محتاج قرار جديد.

---

## 5. الخطة المعدّلة المقترحة

### المبدأ: قسّم الشغل حسب **آلية التخزين**، مش حسب نوع الـ facility

| الصنف | الحقول | الوضع الحالي | الشغل المطلوب | المخاطرة |
|---|---|---|---|---|
| **أ — بيتحل وقت الحساب** | `interest_rate`, `min_interest_rate` | ✅ شغّال (`*_rates` + trigger) | لا شيء | — |
| **أ+ — نفس الآلية، ناقص حقول** | `highest_debt_balance_rate`, `admin_fees_rate` | على الأب، بدون تاريخ | ضيفهم لجداول `*_rates` الموجودة | 🟢 منخفضة |
| **ب — محفور في كل صف** | `limit`, `room` | materialized + cascade | إعادة كتابة + cascade | 🔴 عالية |
| **ج — مقفول عند الإنشاء** | `due_date`, advance rate | ❌ **مكسور** (2.2, 2.4, 2.5) | إصلاح الـ triggers أولاً | 🔴 عالية |

### الترتيب المقترح

**المرحلة ٠ — إصلاحات تمهيدية (قبل أي شغل تجديد)**

دي مش تحسينات، دي **شروط مسبقة**. من غيرها أول تجديد هيفسد بيانات تاريخية:

1. خلّي `due_date` / `max_settlement_days` يتقروا من صف الـ withdrawal نفسه، مش من الأب (2.2)
2. خلّي `before_update_..._limits` يبطّل يعيد اشتقاق الـ `limit` من `lending_information` الحالي (2.4)
3. ضيف بُعد زمني لـ `lending_information` (2.5)
4. صلّح lookup الـ AoC lending rate: فعّل الفلتر + ضيف `ORDER BY` + طابق `contract_id` (3.2)
5. احسم `_max_limit` غير المستخدم: يا إما يتطبّق كسقف يا إما يتشال (3.1) — **ده قرار عمل، مش تقني**

**المرحلة ١ — الصنف أ+** (رسوم أعلى رصيد مدين + الرسوم الإدارية)
وسّع جداول `*_rates` الموجودة. الـ triggers عارفة تقراها بالفعل. **من غير backfill ومن غير cascade.**

**المرحلة ٢ — التجديد الأمامي فقط للـ limit**
اقفل تاريخ السريان على `>= آخر تاريخ statement`. ده **بيلغي مشكلة الـ cascade بالكامل** من أول إصدار.

**المرحلة ٣ — الصنف ج** (بعد إصلاحات المرحلة ٠)

**المرحلة ٤ — التجديد الرجعي** + أمر recalculation مبني على `ProveLimitUpdateCascadeCommand` مع dry-run diff.

### توصيات معمارية

**١. استعمل شكل `*_rates` الموجود بدل جدول "renewal history" جديد.**
الـ triggers عارفة تقراه. ضيف عليه عمود `limit` + علامة تجديد. شاشة «سجل التجديدات» تطلع من نفس الجدول مجاناً.

**٢. متوسّعش `overdraft_against_*_limits` بـ terms.** (راجع 1.2)

**٣. أمر تحقق إجباري.**
يعيد حساب سلسلة facility كاملة جوه transaction بترجع، ويقارن بالمخزَّن. مع كثافة الـ triggers دي ده **شبكة الأمان الوحيدة العملية**، والنمط جاهز في `ProveLimitUpdateCascadeCommand`.

**٤. حدّد في الوثيقة مصير:** `outstanding_balance`، `balance_date`، `oldest_date`، و**حذف/تصحيح تجديد** اتعمل غلط.

---

## 6. ما تم التحقق منه بدقة / ما لم يتم

**اتفحص بالكامل (قراءة الـ ١٦ trigger + فحص بيانات فعلية):**
- مصدر `new.limit` و `new.room` في الأنواع الأربعة — INSERT و UPDATE
- استخدام `_max_limit` في جداول الـ `*_limits` الأربعة
- تطبيق سقف `LEAST` في bank statements النوعين التلقائيين
- فحص بيانات: تجاوز `accumulated_limit` لحد التسهيل، وتجاوز صفوف كشوف الحساب

**لم يتم التحقق منه:**
- مسار استهلاك `fully_secured_overdrafts.cd_or_td_lending_percentage` بالكامل
- دلالات `is_active` على صفوف `*_limits` (soft-delete؟ إعادة حساب؟)
- `LetterOfCreditFacility` و `LetterOfGuaranteeFacility` — نفس العائلة، خارج نطاق الوثيقة
- سلوك الـ triggers تحت تجديدات متعددة متتالية على نفس الـ facility

---

## 7. الخلاصة

الخطة **محتاجة تعديل، مش رفض**. أهم ٤ تغييرات:

1. **احسم دلالة الـ limit أولاً (٢.١)** — ٣ سلوكيات متعارضة عبر ٤ أنواع شقيقة. مينفعش تبني تجديد فوق كده
2. **صحّح §5** — نص الشغل (rate history) معمول، وشغل تاني (materialized limit + cascade) أخطر بكتير من التقدير
3. **اعمل المرحلة ٠ الأول** — قاعدتان من الأربعة مكسورتان في الكود قبل ما نبدأ
4. **ابدأ بـ forward-only** — بيلغي أخطر جزء من أول إصدار

ولو خدت حاجة واحدة بس من التقرير ده: **نفس الرقم — «حد التسهيل» — متنفّذ بـ ٣ دلالات مختلفة عبر ٤ أنواع شقيقة** (٢.١): مجمَّد في Clean و FSO، حالي-وبأثر-رجعي في Assignment of Contract، وغير مطبَّق خالص في Commercial Paper. ده يستاهل قرار فوري بصرف النظر عن مشروع التجديد كله — لأن أي تجديد هيتبني فوق التفاوت ده هيرث تلات سلوكيات مختلفة.
