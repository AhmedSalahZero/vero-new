<?php 
namespace App\Hints;
class HowToAddOneDimensionReport {
// 	علشان نظهر تقرير في ال break down one dimension
// اول حاجه هتدخل علي ال database ملف جدول ال sections وتاخد وليكن العنصر اللي ال id بتاعه 61 اللي هو zones sales breakdown ونعمله duplicate .. ونسمية وليكن مثلا days sales breakdown
// ونديله ال route بتاعه في ال row وليكن مثلا salesBreakdown.day.analysis


// تاني حاجه .. ندخل علي ال RoutesDefinition ونبحث عن 
 // main record here
// ونعمله duplicate بالاسم الجديد .. وخلي بالك ان ال key بتاع ال array دي لو كان day يبقي في ال route name في الداتا بيز اللي انت سميته في الخطوة السابقة لازم يكون day برضو 




// ثالث حاجه هتدخل علي ملف 
// analysis_reports_lists.blade

// وتبحث عن كلمة 
 // first if statement

// ونعمل if statement باسم ال report بدون كلمة Sales Breakdown Analysis يعني لو التقرير كان اسمة في الداتا بيز Days Sales Breakdown Analysis هتكون ال if فيها ال Days فقط ..ونفس الخطوة مع ال 
 // second if statement
// في نفس الملف 

// رابع حاجه هتاخد ملف 
// ZoneAgainstAnalysisReport
// وتعمله duplicate بالاسم الجديد وليكن مثلا DayAgainstAnalysisReport

/**
 * * واخيرا الملف اللي بيتحكم في ال one dimension
 * * SalesBreakdownAgainstAnalysisReport
 * * @ 
 * * salesBreakdownAnalysisIndex
 * * ونفس الامر 
 * * @ salesBreakdownAnalysisResult
 */
}
