{{--
 * * زرار الـ info في عمود الأكشن — بيفتح بوب اب فيه تفاصيل الفواتير
 * * المسوّاة للقراءة فقط
 *
 * * قبل كده مكانش فيه اي طريقة يشوف بيها المستخدم الفواتير اللي اتسوّت
 * * غير انه يفتح شاشة التعديل
 *
 * @var string $url
--}}
<a type="button"
   class="btn btn-secondary btn-outline-hover-info btn-icon js-settlements-info"
   title="{{ __('Settlement Details') }}"
   data-url="{{ $url }}"
   href="#"><i class="fa fa-info-circle"></i></a>
