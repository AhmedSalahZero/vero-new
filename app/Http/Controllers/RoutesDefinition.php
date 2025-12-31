<?php

namespace App\Http\Controllers;

class RoutesDefinition
{

    public function salesTrendAnalysisRoutes()
    {
        return

            [
                // 1 => Zones
                'Zone' => [
                    'name' =>  'zone',
                    'class_path' => 'Analysis\SalesGathering\ZoneAgainstAnalysisReport',
                    'analysis_view' => 'ZoneSalesAnalysisIndex',
                    'analysis_result' => 'ZoneSalesAnalysisResult',
                    'against_view'  => 'index',
                    'against_result'  => 'result',
                    'discount_result'  => 'resultForSalesDiscount',
                    'sub_items' => [
                        'SalesChannels' => 'salesChannels',
                        'Customers' => 'customers',
                        'Countries' => 'countries',
                        'Categories' => 'categories',
                        'Products' => 'products',
                        'Principles' => 'principles',
                        'ProductsItems' => 'Items',
                        'SalesPersons' => 'salesPersons',
                        'BusinessSectors' => 'businessSectors', 'BusinessUnits' => 'businessUnits',
                        'Branches' => 'branches',
                        'SalesDiscount' => 'salesDiscount',
                    ],
                    'avg_items' => [
                        'Products' => 'products',
                        'ProductsItems' => 'Items',
                    ],
                    'has_discount' => true,
                    'has_break_down' => true,
                ],
                // 2 => Sales Channels
                'SalesChannels' => [
                    'name' =>  'salesChannels',
                    'class_path' => 'Analysis\SalesGathering\SalesChannelsAgainstAnalysisReport',
                    'analysis_view' => 'SalesChannelsSalesAnalysisIndex',
                    'analysis_result' => 'SalesChannelsSalesAnalysisResult',
                    'against_view'  => 'index',
                    'against_result'  => 'result',
                    'discount_result'  => 'resultForSalesDiscount',
                    'sub_items' => [
                        'Zones' => 'zones',
                        'Customers' => 'customers',
                        'Countries' => 'countries',
                        'Categories' => 'categories',
                        'Products' => 'products',
                        'Principles' => 'principles',
                        'ProductsItems' => 'Items',
                        'SalesPersons' => 'salesPersons',
                        'BusinessSectors' => 'businessSectors', 'BusinessUnits' => 'businessUnits',
                        'Branches' => 'branches',
                        'SalesDiscount' => 'salesDiscount',
						'day'=>'day'
                    ],
                    'avg_items' => [
                        'Products' => 'products',
                        'ProductsItems' => 'Items',
                    ],
                    'has_discount' => true,
                    'has_break_down' => true,
                ],
                // 3 => Categories
                'Categories' => [
                    'name' =>  'categories',
                    'class_path' => 'Analysis\SalesGathering\CategoriesAgainstAnalysisReport',
                    'analysis_view' => 'CategoriesSalesAnalysisIndex',
                    'analysis_result' => 'CategoriesSalesAnalysisResult',
                    'against_view'  => 'index',
                    'against_result'  => 'result',
                    'discount_result'  => 'resultForSalesDiscount',
                    'sub_items' => [
                        'Zones' => 'zones',
                        'Customers' => 'customers',
                        'Countries' => 'countries',
                        'SalesChannels' => 'salesChannels',
                        'Products' => 'products',
                        'ProductsItems' => 'Items',
                        'SalesPersons' => 'salesPersons',
                        'BusinessSectors' => 'businessSectors', 'BusinessUnits' => 'businessUnits',
                        'Branches' => 'branches',
                        'SalesDiscount' => 'salesDiscount',
						'Principles' => 'principles',
						'day'=>'day'
                    ],
                    'avg_items' => [
                        'Products' => 'products'
                    ],
                    'has_discount' => true,
                    'has_break_down' => true,
                ],
                // 4 => Products
                'Products' => [
                    'name' =>  'products',
                    'class_path' => 'Analysis\SalesGathering\ProductsAgainstAnalysisReport',
                    'against_view'  => 'index',
					'analysis_view'=>'ProductsSalesAnalysisIndex',
					'analysis_result'=>'ProductsSalesAnalysisResult',
                    'against_result'  => 'result',
                    'discount_result'  => 'resultForSalesDiscount',
                    'sub_items' => [
                        'Zones' => 'zones',
                        'Customers' => 'customers',
                        'SalesChannels' => 'salesChannels',
                        'ProductsItems' => 'Items',
                        'SalesPersons' => 'salesPersons',
                        'BusinessSectors' => 'businessSectors', 'BusinessUnits' => 'businessUnits',
                        'Branches' => 'branches',
                        'SalesDiscount' => 'salesDiscount',
                        'Countries' => 'countries',
                        'Principles' => 'principles',
						'day'=>'day'
                    ],
                    'avg_items' => [
                        'ProductsItems' => 'Items',
                    ],
                    'has_discount' => true,
                    'has_break_down' => true,
                ],
                // 5 => Product Items
                'ProductItems' => [
                    'name' =>  'Items',
                    'class_path' => 'Analysis\SalesGathering\SKUsAgainstAnalysisReport',
                    'against_view'  => 'index',
                    'against_result'  => 'result',
                               'analysis_view' => 'CategoriesSalesAnalysisIndex',
                    'analysis_result' => 'ProductsItemsSalesAnalysisResult',
                    'discount_result'  => 'resultForSalesDiscount',
                    'sub_items' => [
                        'Zones' => 'zones',
                        'Customers' => 'customers',
                        'SalesChannels' => 'salesChannels',
                        'SalesPersons' => 'salesPersons',
                        'BusinessSectors' => 'businessSectors', 'BusinessUnits' => 'businessUnits',
                        'Branches' => 'branches',
                        'SalesDiscount' => 'salesDiscount',
                        'Countries' => 'countries',
						'Principles' => 'principles',
						'day'=>'day'
                    ],
                    'has_discount' => true,
                    'has_break_down' => true,
                ],
                // 6 => Branches
                'Branches' => [
                    'name' =>  'branches',
                    'class_path' => 'Analysis\SalesGathering\BranchesAgainstAnalysisReport',
                    'analysis_view' => 'BranchesSalesAnalysisIndex',
                    'analysis_result' => 'BranchesSalesAnalysisResult',
                    'against_view'  => 'index',
                    'against_result'  => 'result',
                    'discount_result'  => 'resultForSalesDiscount',
                    'sub_items' => [
                        'zones' => 'zones',
                        'SalesChannels' => 'salesChannels',
                        'Customers' => 'customers',
                        'Categories' => 'categories',
                        'Products' => 'products',
                        'Principles' => 'principles',
                        'ProductsItems' => 'Items',
                        'SalesPersons' => 'salesPersons',
                        'BusinessSectors' => 'businessSectors', 'BusinessUnits' => 'businessUnits',
                        'SalesDiscount' => 'salesDiscount',
                        'Countries' => 'countries',
						'day'=>'day'
						
                    ],
                      'avg_items' => [
                        'Products' => 'products',
                        'ProductsItems' => 'Items',
                    ],
                    'has_discount' => true,
                    'has_break_down' => true,
                ],
                // 7 => Business Sectors
                'BusinessSectors' => [
                    'name' =>  'businessSectors',
                    'class_path' => 'Analysis\SalesGathering\BusinessSectorsAgainstAnalysisReport',
                    'analysis_view' => 'BusinessSectorsSalesAnalysisIndex',
                    'analysis_result' => 'BusinessSectorsSalesAnalysisResult',
                    'against_view'  => 'index',
                    'against_result'  => 'result',
                    'discount_result'  => 'resultForSalesDiscount',
                    'sub_items' => [
                        'zones' => 'zones',
                        'SalesChannels' => 'salesChannels',
                        'Customers' => 'customers',
                        'Categories' => 'categories',
                        'Products' => 'products',
                        'Principles' => 'principles',
                        'ProductsItems' => 'Items',
                        'SalesPersons' => 'salesPersons',
                        'Branches' => 'branches',
                        'SalesDiscount' => 'salesDiscount',
                        'Countries' => 'countries',
						
                    ],
                    'avg_items' => [
                        'Products' => 'products',
                        'ProductsItems' => 'Items',
                    ],
                    'has_discount' => true,
                    'has_break_down' => true,
                ],
				
				
				'BusinessUnits' => [
                    'name' =>  'businessUnits',
                    'class_path' => 'Analysis\SalesGathering\BusinessUnitsAgainstAnalysisReport',
                    'analysis_view' => 'BusinessUnitsSalesAnalysisIndex',
                    'analysis_result' => 'BusinessUnitsSalesAnalysisResult',
                    'against_view'  => 'index',
                    'against_result'  => 'result',
                    'discount_result'  => 'resultForSalesDiscount',
                    'sub_items' => [
                        'zones' => 'zones',
                        'SalesChannels' => 'salesChannels',
						'BusinessSectors' => 'businessSectors',
                        'Customers' => 'customers',
                        'Categories' => 'categories',
                        'Products' => 'products',
                        'Principles' => 'principles',
                        'ProductsItems' => 'Items',
                        'SalesPersons' => 'salesPersons',
                        'Branches' => 'branches',
                        'SalesDiscount' => 'salesDiscount',
                        'Countries' => 'countries',
                        'day' => 'day',
						
                    ],
                    'avg_items' => [
                        'Products' => 'products',
                        'ProductsItems' => 'Items',
                    ],
                    'has_discount' => true,
                    'has_break_down' => true,
                ],
				
                // 8 => Sales Persons
                'SalesPersons' => [
                    'name' =>  'salesPersons',
                    'class_path' => 'Analysis\SalesGathering\SalesPersonsAgainstAnalysisReport',
                    'against_view'  => 'index',
					'analysis_view'=>true,
                    'against_result'  => 'result',
                    'discount_result'  => 'resultForSalesDiscount',
                    'sub_items' => [
                        'Zones' => 'zones',
                        'SalesChannels' => 'salesChannels',
                        'Categories' => 'categories',
                        'Principles' => 'principles',
                        'Products' => 'products',
                        'ProductItems' => 'Items',
                        'BusinessSectors' => 'businessSectors', 'BusinessUnits' => 'businessUnits',
                        'Branches' => 'branches',
                        'SalesDiscount' => 'salesDiscount',
                        'Countries' => 'countries',
						
                    ],
                    'has_discount' => true,
                    'has_break_down' => true,
                ],
                // 9 => Principles
                'Principles' => [
                    'name' =>  'principles',
                    'has_discount' => true,
                    'has_break_down' => true,
                ],
                // 10 => Customers
                'Customers' => [
                    'name' =>  'customers',
                    /// added by me
				'class_path' => 'Analysis\SalesGathering\CategoriesAgainstAnalysisReport',
                    'analysis_view' => 'CategoriesSalesAnalysisIndex',
                    'analysis_result' => 'CategoriesSalesAnalysisResult',
                    'against_view'  => 'index',
                    'against_result'  => 'result',
                    'discount_result'  => 'resultForSalesDiscount',
                    'sub_items' => [
                        'Categories' => 'categories',
                       'Products' => 'products',
                       'ProductsItems' => 'Items',
                       'Principles' => 'principles',
                       
                    ],
                    /////
                    'has_discount' => true,
                    'has_break_down' => true,
                ],

                  'Invoices' => [
                    'name' =>  'invoices',
                    /// added by me
                    'class_path' => 'Analysis\SalesGathering\InvoicesAgainstAnalysisReport',
                    'analysis_view' => 'InvoicesSalesAnalysisIndex',
                    'analysis_result' => 'InvoicesSalesAnalysisResult',
                    'against_view'  => 'index',
                    'against_result'  => 'result',
                    'discount_result'  => 'resultForSalesDiscount',
                    'sub_items' => [
                        // 'Categories' => 'categories',
                    //    'Products' => 'products',
                                     'SalesPersons' => 'salesPersons',
                    'SalesChannels' => 'salesChannels',
                    'Zones' => 'zones',
                     'BusinessSectors' => 'businessSectors', 'BusinessUnits' => 'businessUnits',
                     'Branches' => 'branches',
                     'Customers' => 'customers',
                       'ProductsItems' => 'Items',
                    ],
                    /////
                    'has_discount' => true,
                    'has_break_down' => true,
                ],

                // 11=> ServiceProvider
                'ServiceProvider' => [
                    'name' =>  'serviceProvider',
                    'has_discount' => false,
                    'has_break_down' => true,
                ],
                // 12 => ServiceProviderTyp
                'ServiceProviderTyp' => [
                    'name' =>  'serviceProviderType',
                    'has_discount' => false,
                    'has_break_down' => true,
                ],
                // 12 => ServiceProviderAge
                'ServiceProviderAge' => [
                    'name' =>  'serviceProviderAge',
                    'has_discount' => false,
                    'has_break_down' => true,
                ],
                // 13 => Sales DiscountS
                'SalesDiscountS' => [
                    'name' =>  'salesDiscounts',
                    'has_discount' => false,
                    'has_break_down' => true,
                ],
                // 14 => Countries
                'Countries' => [
                    'name' =>  'country',
                    'class_path' => 'Analysis\SalesGathering\CountriesAgainstAnalysisReport',
                    'analysis_view' => 'CountriesSalesAnalysisIndex',
                    'analysis_result' => 'CountriesSalesAnalysisResult',
                    'against_view'  => 'index',
                    'against_result'  => 'result',
                    'discount_result'  => 'resultForSalesDiscount',
                    'sub_items' => [
                        'Zones' => 'zones',
                        'SalesChannels' => 'salesChannels',
                        'Customers' => 'customers',
                        'Categories' => 'categories',
                        'Products' => 'products',
                        'Principles' => 'principles',
                        'ProductsItems' => 'Items',
                        'SalesPersons' => 'salesPersons',
                        'BusinessSectors' => 'businessSectors', 'BusinessUnits' => 'businessUnits',
                        'Branches' => 'branches',
                        'SalesDiscount' => 'salesDiscount',
                    ],
                    'avg_items' => [
                        'Products' => 'products',
                        'ProductsItems' => 'Items',
                    ],
                    'has_discount' => true,
                    'has_break_down' => true,
                ],
				// main record here
				'day' => [
                    'name' =>  'day',
                    'class_path' => 'Analysis\SalesGathering\DayAgainstAnalysisReport',
                    'analysis_view' => 'DaySalesAnalysisIndex',
                    'analysis_result' => 'DaySalesAnalysisResult',
                    'against_view'  => 'index',
                    'against_result'  => 'result',
                    'discount_result'  => 'resultForSalesDiscount',
                    'sub_items' => [
						'Zones' => 'zones',
                        'SalesChannels' => 'salesChannels',
                        'Customers' => 'customers',
                        'Countries' => 'countries',
                        'Categories' => 'categories',
                        'Products' => 'products',
                        'Principles' => 'principles',
                        'ProductsItems' => 'Items',
                        'SalesPersons' => 'salesPersons',
                        'BusinessSectors' => 'businessSectors', 'BusinessUnits' => 'businessUnits',
                        'Branches' => 'branches',
                        'SalesDiscount' => 'salesDiscount',
                    ],
                    'avg_items' => [
                        'Products' => 'products',
                        'ProductsItems' => 'Items',
                    ],
                    'has_discount' => true,
                    'has_break_down' => true,
                ],
				

            ];
    }
    public function  twoDimensionalBreakdownRoutes()
    {
        return [
            // 1 => Zones
            'Zones' => [
                'name' =>  'zone',
                'is_provider' => false,
                'sub_items' => [
                    'SalesChannels' => 'salesChannels',
                ]
            ],
            // 1 => businessSectors
            'BusinessSectors' => [
                'name' =>  'businessSectors',
                'is_provider' => false,
                'sub_items' => [
                    'SalesChannels' => 'salesChannels',
                ]
				
            ],
			
			
			'BusinessUnits' => [
                'name' =>  'businessUnits',
                'is_provider' => false,
                'sub_items' => [
                    'SalesChannels' => 'salesChannels',
					 'day'=>'day'
                ]
				
            ],
			
			
            // 2 => Sales Channels
            'SalesChannels' => [
                'name' =>  'salesChannels',
                'is_provider' => false,
                'sub_items' => [
                    'Zones' => 'zones',
					'day'=>'day'
							
                ]
            ],
            // 3 => Products
            'Products' => [
                'name' =>  'products',
                'is_provider' => false,
                'sub_items' => [
                    'Zones' => 'zones',
                    'SalesChannels' => 'salesChannels',
                    'BusinessSectors' => 'businessSectors', 'BusinessUnits' => 'businessUnits',
          		      'branches' => 'branches',
					  'day'=>'day'
                ]
            ],
            // 4 => ProductItems
            'ProductItems' => [
                'name' =>  'Items',
                'is_provider' => false,
                'sub_items' => [
                    'Zones' => 'zones',
                    'SalesChannels' => 'salesChannels',
                    'BusinessSectors' => 'businessSectors', 'BusinessUnits' => 'businessUnits',
                    'Branches'=>'branches',
					'day'=>'day'
                ]
            ],
            // 5 => Categories
            'Categories' => [
                'name' =>  'categories',
                'is_provider' => false,
                'sub_items' => [
                    'Zones' => 'zones',
                    'SalesChannels' => 'salesChannels',
                    'BusinessSectors' => 'businessSectors', 'BusinessUnits' => 'businessUnits',
                    'Branches' => 'branches',
					'day'=>'day'
					]
            ],
            // 6 => Customers
            'Customers' => [
                'name' =>  'customers',
                'is_provider' => false,
                'sub_items' => [
                    'Zones' => 'zones',
                    'SalesChannels' => 'salesChannels',
                    'BusinessSectors' => 'businessSectors', 'BusinessUnits' => 'businessUnits',
                ]
            ],
            // 7 => Branches
            'Branches' => [
                'name' =>  'branches',
                'is_provider' => false,
                'sub_items' => [
                    'SalesChannels' => 'salesChannels',
					'day'=>'day'
                    // 'productI' => 'businessSectors',
                ]
            ],
            // 8 => ServiceProviders
            'ServiceProviders' => [
                'name' =>  'serviceProviders',
                'is_provider' => true,
                'sub_items' => [
                    'BusinessSectors' => 'businessSectors', 'BusinessUnits' => 'businessUnits',
                    'Branches' => 'branches',
                    'SalesChannels' => 'salesChannels',
                    'Products' => 'products',
                ]
            ],
            // 9 => ServiceProvidersType
            'ServiceProvidersType' => [
                'name' =>  'serviceProvidersType',
                'is_provider' => true,
                'sub_items' => [
                    'BusinessSectors' => 'businessSectors', 'BusinessUnits' => 'businessUnits',
                    'Branches' => 'branches',
                    'SalesChannels' => 'salesChannels',
                    'Products' => 'products',
                ]
            ],
            // 10 => ServiceProvidersBirthYear
            'ServiceProvidersBirthYear' => [
                'name' =>  'serviceProvidersBirthYear',
                'is_provider' => true,
                'sub_items' => [
                    'BusinessSectors' => 'businessSectors', 'BusinessUnits' => 'businessUnits',
                    'Branches' => 'branches',
                    'SalesChannels' => 'salesChannels',
                    'Products' => 'products',
                ]
            ],
            // 11 => Countries
            'Countries' => [
                'name' =>  'countries',
                'is_provider' => false,
                'sub_items' => [
                    'SalesChannels' => 'salesChannels',
                    'BusinessSectors' => 'businessSectors', 'BusinessUnits' => 'businessUnits',
                    'ProductsItems' => 'Items',
                ]
            ],
			
        ];
    }


     public function  twoDimensionalRankingsRoutes()
    {
        return [

             'Branches' => [
                'name' =>  'branches',
                'is_provider' => false,
                'sub_items' => [
                    'ProductsItems' => 'Items',
                    'Products' => 'Products',
                ]
            ],

        ];
    }

}
