<?php

namespace App\Helpers;

class PropertyLocationHelper
{
    public static function getCountries(): array
    {
        return [
            ['id' => 'egypt', 'name' => __('Egypt')],
            ['id' => 'saudi_arabia', 'name' => __('Saudi Arabia')],
            ['id' => 'uae', 'name' => __('United Arab Emirates')],
            ['id' => 'kuwait', 'name' => __('Kuwait')],
            ['id' => 'qatar', 'name' => __('Qatar')],
            ['id' => 'bahrain', 'name' => __('Bahrain')],
            ['id' => 'oman', 'name' => __('Oman')],
        ];
    }

    public static function getCitiesByCountry(string $countryId): array
    {
        $cities = [
            'egypt' => [
                ['id' => 'cairo', 'name' => __('Cairo')],
                ['id' => 'alexandria', 'name' => __('Alexandria')],
                ['id' => 'giza', 'name' => __('Giza')],
                ['id' => 'sharm_el_sheikh', 'name' => __('Sharm El Sheikh')],
                ['id' => 'luxor', 'name' => __('Luxor')],
                ['id' => 'aswan', 'name' => __('Aswan')],
                ['id' => 'hurghada', 'name' => __('Hurghada')],
            ],
            'saudi_arabia' => [
                ['id' => 'riyadh', 'name' => __('Riyadh')],
                ['id' => 'jeddah', 'name' => __('Jeddah')],
                ['id' => 'mecca', 'name' => __('Mecca')],
                ['id' => 'medina', 'name' => __('Medina')],
                ['id' => 'dammam', 'name' => __('Dammam')],
                ['id' => 'khobar', 'name' => __('Khobar')],
            ],
            'uae' => [
                ['id' => 'dubai', 'name' => __('Dubai')],
                ['id' => 'abu_dhabi', 'name' => __('Abu Dhabi')],
                ['id' => 'sharjah', 'name' => __('Sharjah')],
                ['id' => 'ajman', 'name' => __('Ajman')],
                ['id' => 'ras_al_khaimah', 'name' => __('Ras Al Khaimah')],
                ['id' => 'fujairah', 'name' => __('Fujairah')],
                ['id' => 'umm_al_quwain', 'name' => __('Umm Al Quwain')],
            ],
            'kuwait' => [
                ['id' => 'kuwait_city', 'name' => __('Kuwait City')],
                ['id' => 'al_ahmadi', 'name' => __('Al Ahmadi')],
                ['id' => 'al_farwaniyah', 'name' => __('Al Farwaniyah')],
                ['id' => 'hawalli', 'name' => __('Hawalli')],
                ['id' => 'al_jahra', 'name' => __('Al Jahra')],
            ],
            'qatar' => [
                ['id' => 'doha', 'name' => __('Doha')],
                ['id' => 'al_rayyan', 'name' => __('Al Rayyan')],
                ['id' => 'al_wakrah', 'name' => __('Al Wakrah')],
                ['id' => 'al_khor', 'name' => __('Al Khor')],
            ],
            'bahrain' => [
                ['id' => 'manama', 'name' => __('Manama')],
                ['id' => 'muharraq', 'name' => __('Muharraq')],
                ['id' => 'rifa', 'name' => __('Rifa')],
                ['id' => 'hamad_town', 'name' => __('Hamad Town')],
            ],
            'oman' => [
                ['id' => 'muscat', 'name' => __('Muscat')],
                ['id' => 'salalah', 'name' => __('Salalah')],
                ['id' => 'sohar', 'name' => __('Sohar')],
                ['id' => 'nizwa', 'name' => __('Nizwa')],
            ],
        ];

        return $cities[$countryId] ?? [];
    }

    public static function getAreasByCity(string $countryId, string $cityId): array
    {
        $areas = [
            'egypt' => [
                'cairo' => [
                    ['id' => 'zamalek', 'name' => __('Zamalek')],
                    ['id' => 'maadi', 'name' => __('Maadi')],
                    ['id' => 'heliopolis', 'name' => __('Heliopolis')],
                    ['id' => 'nasr_city', 'name' => __('Nasr City')],
                    ['id' => 'new_cairo', 'name' => __('New Cairo')],
                    ['id' => '6_october', 'name' => __('6 October')],
                ],
                'alexandria' => [
                    ['id' => 'montaza', 'name' => __('Montaza')],
                    ['id' => 'stanley', 'name' => __('Stanley')],
                    ['id' => 'sidi_bishr', 'name' => __('Sidi Bishr')],
                    ['id' => 'gleem', 'name' => __('Gleem')],
                ],
                'giza' => [
                    ['id' => 'dokki', 'name' => __('Dokki')],
                    ['id' => 'mohandessin', 'name' => __('Mohandessin')],
                    ['id' => 'agouza', 'name' => __('Agouza')],
                ],
            ],
            'uae' => [
                'dubai' => [
                    ['id' => 'downtown_dubai', 'name' => __('Downtown Dubai')],
                    ['id' => 'dubai_marina', 'name' => __('Dubai Marina')],
                    ['id' => 'palm_jumeirah', 'name' => __('Palm Jumeirah')],
                    ['id' => 'business_bay', 'name' => __('Business Bay')],
                    ['id' => 'jbr', 'name' => __('JBR')],
                ],
                'abu_dhabi' => [
                    ['id' => 'yas_island', 'name' => __('Yas Island')],
                    ['id' => 'saadiyat_island', 'name' => __('Saadiyat Island')],
                    ['id' => 'al_reef', 'name' => __('Al Reef')],
                ],
            ],
            'saudi_arabia' => [
                'riyadh' => [
                    ['id' => 'olaya', 'name' => __('Olaya')],
                    ['id' => 'malaz', 'name' => __('Malaz')],
                    ['id' => 'diplomatic_quarter', 'name' => __('Diplomatic Quarter')],
                ],
                'jeddah' => [
                    ['id' => 'corniche', 'name' => __('Corniche')],
                    ['id' => 'al_hamra', 'name' => __('Al Hamra')],
                ],
            ],
            'kuwait' => [
                'kuwait_city' => [
                    ['id' => 'salmiya', 'name' => __('Salmiya')],
                    ['id' => 'sharq', 'name' => __('Sharq')],
                    ['id' => 'dasma', 'name' => __('Dasma')],
                ],
            ],
            'qatar' => [
                'doha' => [
                    ['id' => 'west_bay', 'name' => __('West Bay')],
                    ['id' => 'the_pearl', 'name' => __('The Pearl')],
                    ['id' => 'lusail', 'name' => __('Lusail')],
                ],
            ],
            'bahrain' => [
                'manama' => [
                    ['id' => 'seef', 'name' => __('Seef')],
                    ['id' => 'juffair', 'name' => __('Juffair')],
                ],
            ],
            'oman' => [
                'muscat' => [
                    ['id' => 'qurum', 'name' => __('Qurum')],
                    ['id' => 'shati_al_qurum', 'name' => __('Shati Al Qurum')],
                ],
            ],
        ];

        return $areas[$countryId][$cityId] ?? [];
    }

    public static function getFormattedCountries(): array
    {
        $countries = self::getCountries();
        return array_map(function ($country) {
            return [
                'id' => $country['id'],
                'title' => $country['name'],
            ];
        }, $countries);
    }

    public static function getFormattedCities(string $countryId): array
    {
        $cities = self::getCitiesByCountry($countryId);
        return array_map(function ($city) {
            return [
                'id' => $city['id'],
                'title' => $city['name'],
            ];
        }, $cities);
    }

    public static function getFormattedAreas(string $countryId, string $cityId): array
    {
        $areas = self::getAreasByCity($countryId, $cityId);
        return array_map(function ($area) {
            return [
                'id' => $area['id'],
                'title' => $area['name'],
            ];
        }, $areas);
    }
}
