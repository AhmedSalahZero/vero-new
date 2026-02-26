<?php

namespace Database\Seeders;

use App\Models\PropertyManagement\Country;
use App\Models\PropertyManagement\Governorate;
use App\Models\PropertyManagement\City;
use Illuminate\Database\Seeder;

class PropertyManagementLocationsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Get all companies (or use a specific company ID)
        $companies = \App\Models\Company::all();
        
        foreach ($companies as $company) {
            $this->seedLocationsForCompany($company->id);
        }
    }
    
    private function seedLocationsForCompany($companyId)
    {
        // Egypt
        $egypt = Country::create([
            'name_en' => 'Egypt',
            'name_ar' => 'مصر',
            'company_id' => $companyId,
        ]);
        
        $cairo = Governorate::create([
            'country_id' => $egypt->id,
            'name_en' => 'Cairo',
            'name_ar' => 'القاهرة',
            'company_id' => $companyId,
        ]);
        
        City::create(['governorate_id' => $cairo->id, 'name_en' => 'Nasr City', 'name_ar' => 'مدينة نصر', 'company_id' => $companyId]);
        City::create(['governorate_id' => $cairo->id, 'name_en' => 'Maadi', 'name_ar' => 'المعادي', 'company_id' => $companyId]);
        City::create(['governorate_id' => $cairo->id, 'name_en' => 'Heliopolis', 'name_ar' => 'مصر الجديدة', 'company_id' => $companyId]);
        City::create(['governorate_id' => $cairo->id, 'name_en' => 'Downtown', 'name_ar' => 'وسط البلد', 'company_id' => $companyId]);
        City::create(['governorate_id' => $cairo->id, 'name_en' => 'Zamalek', 'name_ar' => 'الزمالك', 'company_id' => $companyId]);
        
        $giza = Governorate::create([
            'country_id' => $egypt->id,
            'name_en' => 'Giza',
            'name_ar' => 'الجيزة',
            'company_id' => $companyId,
        ]);
        
        City::create(['governorate_id' => $giza->id, 'name_en' => '6th of October', 'name_ar' => '6 أكتوبر', 'company_id' => $companyId]);
        City::create(['governorate_id' => $giza->id, 'name_en' => 'Sheikh Zayed', 'name_ar' => 'الشيخ زايد', 'company_id' => $companyId]);
        City::create(['governorate_id' => $giza->id, 'name_en' => 'Dokki', 'name_ar' => 'الدقي', 'company_id' => $companyId]);
        City::create(['governorate_id' => $giza->id, 'name_en' => 'Mohandessin', 'name_ar' => 'المهندسين', 'company_id' => $companyId]);
        City::create(['governorate_id' => $giza->id, 'name_en' => 'Haram', 'name_ar' => 'الهرم', 'company_id' => $companyId]);
        
        $alexandria = Governorate::create([
            'country_id' => $egypt->id,
            'name_en' => 'Alexandria',
            'name_ar' => 'الإسكندرية',
            'company_id' => $companyId,
        ]);
        
        City::create(['governorate_id' => $alexandria->id, 'name_en' => 'Miami', 'name_ar' => 'ميامي', 'company_id' => $companyId]);
        City::create(['governorate_id' => $alexandria->id, 'name_en' => 'Smouha', 'name_ar' => 'سموحة', 'company_id' => $companyId]);
        City::create(['governorate_id' => $alexandria->id, 'name_en' => 'Sidi Gaber', 'name_ar' => 'سيدي جابر', 'company_id' => $companyId]);
        City::create(['governorate_id' => $alexandria->id, 'name_en' => 'Stanley', 'name_ar' => 'ستانلي', 'company_id' => $companyId]);
        City::create(['governorate_id' => $alexandria->id, 'name_en' => 'Montaza', 'name_ar' => 'المنتزه', 'company_id' => $companyId]);
        
        // Saudi Arabia
        $saudiArabia = Country::create([
            'name_en' => 'Saudi Arabia',
            'name_ar' => 'المملكة العربية السعودية',
            'company_id' => $companyId,
        ]);
        
        $riyadh = Governorate::create([
            'country_id' => $saudiArabia->id,
            'name_en' => 'Riyadh',
            'name_ar' => 'الرياض',
            'company_id' => $companyId,
        ]);
        
        City::create(['governorate_id' => $riyadh->id, 'name_en' => 'Al Olaya', 'name_ar' => 'العليا', 'company_id' => $companyId]);
        City::create(['governorate_id' => $riyadh->id, 'name_en' => 'Al Malaz', 'name_ar' => 'الملز', 'company_id' => $companyId]);
        City::create(['governorate_id' => $riyadh->id, 'name_en' => 'Al Naseem', 'name_ar' => 'النسيم', 'company_id' => $companyId]);
        City::create(['governorate_id' => $riyadh->id, 'name_en' => 'Al Sahafa', 'name_ar' => 'الصحافة', 'company_id' => $companyId]);
        City::create(['governorate_id' => $riyadh->id, 'name_en' => 'King Fahd', 'name_ar' => 'الملك فهد', 'company_id' => $companyId]);
        
        $jeddah = Governorate::create([
            'country_id' => $saudiArabia->id,
            'name_en' => 'Jeddah',
            'name_ar' => 'جدة',
            'company_id' => $companyId,
        ]);
        
        City::create(['governorate_id' => $jeddah->id, 'name_en' => 'Al Hamra', 'name_ar' => 'الحمراء', 'company_id' => $companyId]);
        City::create(['governorate_id' => $jeddah->id, 'name_en' => 'Al Rawdah', 'name_ar' => 'الروضة', 'company_id' => $companyId]);
        City::create(['governorate_id' => $jeddah->id, 'name_en' => 'Al Salamah', 'name_ar' => 'السلامة', 'company_id' => $companyId]);
        City::create(['governorate_id' => $jeddah->id, 'name_en' => 'Al Shati', 'name_ar' => 'الشاطئ', 'company_id' => $companyId]);
        City::create(['governorate_id' => $jeddah->id, 'name_en' => 'Al Zahra', 'name_ar' => 'الزهراء', 'company_id' => $companyId]);
        
        // UAE
        $uae = Country::create([
            'name_en' => 'UAE',
            'name_ar' => 'الإمارات العربية المتحدة',
            'company_id' => $companyId,
        ]);
        
        $dubai = Governorate::create([
            'country_id' => $uae->id,
            'name_en' => 'Dubai',
            'name_ar' => 'دبي',
            'company_id' => $companyId,
        ]);
        
        City::create(['governorate_id' => $dubai->id, 'name_en' => 'Downtown', 'name_ar' => 'وسط المدينة', 'company_id' => $companyId]);
        City::create(['governorate_id' => $dubai->id, 'name_en' => 'Marina', 'name_ar' => 'مارينا', 'company_id' => $companyId]);
        City::create(['governorate_id' => $dubai->id, 'name_en' => 'JBR', 'name_ar' => 'جميرا بيتش', 'company_id' => $companyId]);
        City::create(['governorate_id' => $dubai->id, 'name_en' => 'Business Bay', 'name_ar' => 'الخليج التجاري', 'company_id' => $companyId]);
        City::create(['governorate_id' => $dubai->id, 'name_en' => 'Palm Jumeirah', 'name_ar' => 'نخلة جميرا', 'company_id' => $companyId]);
        
        $abuDhabi = Governorate::create([
            'country_id' => $uae->id,
            'name_en' => 'Abu Dhabi',
            'name_ar' => 'أبوظبي',
            'company_id' => $companyId,
        ]);
        
        City::create(['governorate_id' => $abuDhabi->id, 'name_en' => 'Al Reem Island', 'name_ar' => 'جزيرة الريم', 'company_id' => $companyId]);
        City::create(['governorate_id' => $abuDhabi->id, 'name_en' => 'Yas Island', 'name_ar' => 'جزيرة ياس', 'company_id' => $companyId]);
        City::create(['governorate_id' => $abuDhabi->id, 'name_en' => 'Saadiyat Island', 'name_ar' => 'جزيرة السعديات', 'company_id' => $companyId]);
        City::create(['governorate_id' => $abuDhabi->id, 'name_en' => 'Al Raha', 'name_ar' => 'الراحة', 'company_id' => $companyId]);
        City::create(['governorate_id' => $abuDhabi->id, 'name_en' => 'Khalifa City', 'name_ar' => 'مدينة خليفة', 'company_id' => $companyId]);
        
        // Kuwait
        $kuwait = Country::create([
            'name_en' => 'Kuwait',
            'name_ar' => 'الكويت',
            'company_id' => $companyId,
        ]);
        
        $kuwaitCity = Governorate::create([
            'country_id' => $kuwait->id,
            'name_en' => 'Kuwait City',
            'name_ar' => 'مدينة الكويت',
            'company_id' => $companyId,
        ]);
        
        City::create(['governorate_id' => $kuwaitCity->id, 'name_en' => 'Sharq', 'name_ar' => 'الشرق', 'company_id' => $companyId]);
        City::create(['governorate_id' => $kuwaitCity->id, 'name_en' => 'Dasman', 'name_ar' => 'دسمان', 'company_id' => $companyId]);
        City::create(['governorate_id' => $kuwaitCity->id, 'name_en' => 'Qibla', 'name_ar' => 'القبلة', 'company_id' => $companyId]);
        City::create(['governorate_id' => $kuwaitCity->id, 'name_en' => 'Mirqab', 'name_ar' => 'المرقاب', 'company_id' => $companyId]);
        City::create(['governorate_id' => $kuwaitCity->id, 'name_en' => 'Salhiya', 'name_ar' => 'الصالحية', 'company_id' => $companyId]);
        
        // Qatar
        $qatar = Country::create([
            'name_en' => 'Qatar',
            'name_ar' => 'قطر',
            'company_id' => $companyId,
        ]);
        
        $doha = Governorate::create([
            'country_id' => $qatar->id,
            'name_en' => 'Doha',
            'name_ar' => 'الدوحة',
            'company_id' => $companyId,
        ]);
        
        City::create(['governorate_id' => $doha->id, 'name_en' => 'West Bay', 'name_ar' => 'الخليج الغربي', 'company_id' => $companyId]);
        City::create(['governorate_id' => $doha->id, 'name_en' => 'Al Sadd', 'name_ar' => 'السد', 'company_id' => $companyId]);
        City::create(['governorate_id' => $doha->id, 'name_en' => 'Al Dafna', 'name_ar' => 'الدفنة', 'company_id' => $companyId]);
        City::create(['governorate_id' => $doha->id, 'name_en' => 'Lusail', 'name_ar' => 'لوسيل', 'company_id' => $companyId]);
        City::create(['governorate_id' => $doha->id, 'name_en' => 'The Pearl', 'name_ar' => 'اللؤلؤة', 'company_id' => $companyId]);
        
        // Bahrain
        $bahrain = Country::create([
            'name_en' => 'Bahrain',
            'name_ar' => 'البحرين',
            'company_id' => $companyId,
        ]);
        
        $manama = Governorate::create([
            'country_id' => $bahrain->id,
            'name_en' => 'Manama',
            'name_ar' => 'المنامة',
            'company_id' => $companyId,
        ]);
        
        City::create(['governorate_id' => $manama->id, 'name_en' => 'Diplomatic Area', 'name_ar' => 'المنطقة الدبلوماسية', 'company_id' => $companyId]);
        City::create(['governorate_id' => $manama->id, 'name_en' => 'Adliya', 'name_ar' => 'العدلية', 'company_id' => $companyId]);
        City::create(['governorate_id' => $manama->id, 'name_en' => 'Juffair', 'name_ar' => 'الجفير', 'company_id' => $companyId]);
        City::create(['governorate_id' => $manama->id, 'name_en' => 'Seef', 'name_ar' => 'السيف', 'company_id' => $companyId]);
        City::create(['governorate_id' => $manama->id, 'name_en' => 'Sanabis', 'name_ar' => 'السنابس', 'company_id' => $companyId]);
        
        // Oman
        $oman = Country::create([
            'name_en' => 'Oman',
            'name_ar' => 'عمان',
            'company_id' => $companyId,
        ]);
        
        $muscat = Governorate::create([
            'country_id' => $oman->id,
            'name_en' => 'Muscat',
            'name_ar' => 'مسقط',
            'company_id' => $companyId,
        ]);
        
        City::create(['governorate_id' => $muscat->id, 'name_en' => 'Al Khuwair', 'name_ar' => 'الخوير', 'company_id' => $companyId]);
        City::create(['governorate_id' => $muscat->id, 'name_en' => 'Qurum', 'name_ar' => 'القرم', 'company_id' => $companyId]);
        City::create(['governorate_id' => $muscat->id, 'name_en' => 'Al Ghubra', 'name_ar' => 'الغبرة', 'company_id' => $companyId]);
        City::create(['governorate_id' => $muscat->id, 'name_en' => 'Ruwi', 'name_ar' => 'روي', 'company_id' => $companyId]);
        City::create(['governorate_id' => $muscat->id, 'name_en' => 'Madinat Sultan Qaboos', 'name_ar' => 'مدينة السلطان قابوس', 'company_id' => $companyId]);
        
        echo "Seeded locations for company {$companyId}\n";
    }
}
