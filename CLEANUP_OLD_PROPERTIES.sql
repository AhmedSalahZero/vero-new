-- =====================================================
-- تنظيف Properties القديمة التي تم إنشاؤها أثناء الاختبار
-- Clean up old test properties
-- =====================================================

USE property_management;

-- اعرض كل الـ properties للشركة 31
-- Show all properties for company 31
SELECT id, name, code, property_type, parent_property_id, created_at 
FROM properties 
WHERE company_id = 31
ORDER BY created_at DESC;

-- إذا أردت حذف property معينة بالـ code (مثال: a212)
-- If you want to delete a specific property by code (example: a212)
-- DELETE FROM properties WHERE code = 'a212' AND company_id = 31;

-- إذا أردت حذف كل الـ properties للشركة 31 (استخدم بحذر!)
-- If you want to delete ALL properties for company 31 (use carefully!)
-- DELETE FROM properties WHERE company_id = 31;

-- إذا أردت حذف الـ properties التي تم إنشاؤها اليوم فقط
-- If you want to delete only today's properties
-- DELETE FROM properties 
-- WHERE company_id = 31 
-- AND DATE(created_at) = CURDATE();

-- بعد الحذف، تحقق من النتيجة
-- After deletion, verify the result
SELECT id, name, code, property_type, parent_property_id, created_at 
FROM properties 
WHERE company_id = 31
ORDER BY created_at DESC;
