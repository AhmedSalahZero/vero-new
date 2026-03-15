<?php
Route::group(['prefix'=>PROPERTY_MANAGEMENT_SERVICE_URL_PREFIX,'middleware'=>'isPropertyManagement'], function () {
    Route::get('fixed-payments-at-end/{study}', 'Loans2Controller@create')->name('property.management.fixed.loan.fixed.at.end');
    Route::get('fixed-payments-at-beginning/{study}', 'Loans2Controller@create')->name('property.management.fixed.loan.fixed.at.beginning');
    Route::get('calculate-loan-amount/{study}', 'Loans2Controller@create')->name('property.management.calc.loan.amount');
    Route::get('calculate-interest-rate/{study}', 'Loans2Controller@create')->name('property.management.calc.interest.percentage');
    Route::get('variable-payments/{study}', 'Loans2Controller@create')->name('property.management.variable.payments');
    Route::group(['namespace'=>'PropertyManagements'], function () {
		
        route::get('study', 'StudyController@index')->name('property.management.view.study');
        route::get('study/create', 'StudyController@create')->name('property.management.create.study');
        Route::get('study/{study}/edit', 'StudyController@edit')->name('property.management.edit.study');
        Route::put('study/{study}/update', 'StudyController@update')->name('property.management.update.study');
        route::post('study', 'StudyController@store')->name('store.property.management.services');
        route::delete('study/{study}/delete', 'StudyController@destroy')->name('property.management.study.destroy');
        Route::post('/copy/{study}', 'CopyStudyController@index')->name('property.management.copy.study');
		Route::get('property-dashboard', 'DashboardController@viewPropertyDashboard')->name('property.management.view.property.dashboard');            
		Route::get('property-cashflow-forecast-dashboard', 'DashboardController@viewPropertyCashflowForecastDashboard')->name('property.management.view.property.cashflow.forecast.dashboard');   
		Route::get('dashboard/cashflow-forecast-old-data', 'DashboardController@getCashflowForecastDashboardOldData');
		Route::post('dashboard/cashflow-forecast-submit', 'DashboardController@submitCashflowForecast')->name('property.management.dashboard.cashflow-forecast.submit');
        route::get('properties', 'PropertiesController@index')->name('property.management.view.properties');
        route::get('properties/create', 'PropertiesController@create')->name('property.management.create.properties');
        route::get('properties/{property}/edit', 'PropertiesController@edit')->name('property.management.edit.properties');
        route::get('properties/properties-old-data', 'PropertiesController@getOldData');
        route::post('properties/create', 'PropertiesController@store')->name('property.management.store.properties');
        route::put('properties/{property}/update', 'PropertiesController@update')->name('property.management.update.properties');
        route::delete('properties/{property}/destroy', 'PropertiesController@destroy')->name('property.management.destroy.properties');
		route::post('properties/{property}/due-installments', 'PropertiesController@storeDueInstallments')->name('property-managements.properties.due-installments.store');
        
		route::get('properties/{property}/property-expenses', 'PropertyExpensesController@create')->name('property.management.create.property.expenses');
		route::get('properties/{property}/property-expenses-old-data', 'PropertyExpensesController@getOldData');
		route::post('properties/{property}/property-expenses', 'PropertyExpensesController@store')->name('property.management.store.property.expenses');
		route::delete('properties/{property}/property-expenses/{propertyExpense}/destroy', 'PropertyExpensesController@destroy')->name('property.management.destroy.property.expenses');
		
        // Contracts routes
        route::get('properties/{property}/contracts', 'ContractsController@index')->name('property-managements.properties.contracts.index');
        route::get('properties/{property}/contracts/get', 'ContractsController@getContracts')->name('property-managements.properties.contracts.get');
        route::get('properties/{property}/contracts/create', 'ContractsController@create')->name('property-managements.properties.contracts.create');
        route::get('properties/{property}/contracts/old-data', 'ContractsController@getOldData')->name('property-managements.properties.contracts.old-data');
        route::post('properties/{property}/contracts/store', 'ContractsController@store')->name('property-managements.properties.contracts.store');
        route::get('properties/{property}/contracts/{contract}/edit', 'ContractsController@edit')->name('property-managements.properties.contracts.edit');
        route::put('properties/{property}/contracts/{contract}/update', 'ContractsController@update')->name('property-managements.properties.contracts.update');

        route::delete('properties/{property}/contracts/{contract}/destroy', 'ContractsController@destroy')->name('property-managements.properties.contracts.destroy');
        route::post('properties/{property}/contracts/{contract}/mark-as-finished', 'ContractsController@markAsFinished')->name('property-managements.properties.contracts.mark-as-finished');
        route::get('properties/{property}/contracts/{contract}/renew-form', 'ContractsController@showRenewForm')->name('property-managements.properties.contracts.renew-form');
        route::post('properties/{property}/contracts/{contract}/renew', 'ContractsController@renew')->name('property-managements.properties.contracts.renew');
		
		
	
		
		
		Route::get('foreign-exchange-rate', 'ForeignExchangeRateController@index')->name('view.property.management.foreign.exchange.rate');
		Route::post('foreign-exchange-rate', 'ForeignExchangeRateController@store')->name('store.property.management.foreign.exchange.rate');
		Route::get('foreign-exchange-rate/edit/{foreignExchangeRate}', 'ForeignExchangeRateController@edit')->name('edit.property.management.foreign.exchange.rate');
		Route::patch('foreign-exchange-rate/edit/{foreignExchangeRate}', 'ForeignExchangeRateController@update')->name('update.property.management.foreign.exchange.rate');
		Route::delete('delete-foreign-exchange-rate/edit/{foreignExchangeRate}', 'ForeignExchangeRateController@destroy')->name('delete.property.management.foreign.exchange.rate');

		
                    
                    
        // route::get('existing-branches/create', 'ExistingBranchesController@create')->name('create.existing.branches');
        // route::post('existing-branches/create', 'ExistingBranchesController@store')->name('store.existing.branches');
                    
                    
        route::get('departments', 'DepartmentController@index')->name('property.management.view.departments');
        route::get('departments/create/{type}', 'DepartmentController@create')->name('property.management.create.departments');
        route::post('departments/create/{type}', 'DepartmentController@store')->name('property.management.store.departments');
        route::get('departments/{department}/edit/{type}', 'DepartmentController@edit')->name('property.management.edit.departments');
        route::put('departments/{department}/update/{type}', 'DepartmentController@update')->name('property.management.update.departments');
        route::delete('departments/{department}/destroy', 'DepartmentController@destroy')->name('property.management.departments.destroy');
                    
      
        route::get('expense-names', 'ExpenseController@index')->name('property.management.view.expense.names');
        route::get('expense-names/create', 'ExpenseController@create')->name('property.management.create.expense.names');
        route::post('expense-names/create', 'ExpenseController@store')->name('property.management.store.expense.names');
        route::get('expense-names/{expenseType}/edit', 'ExpenseController@edit')->name('property.management.edit.expense.names');
        route::put('expense-names/{expenseType}/update', 'ExpenseController@update')->name('property.management.update.expense.names');
        route::delete('expense-names/{expenseType}/destroy', 'ExpenseController@destroy')->name('property.management.expense.names.destroy');
                  
		
		route::get('tenants', 'TenantsController@index')->name('property.management.view.tenants');
        route::get('tenants/create', 'TenantsController@create')->name('property.management.create.tenants');
        route::post('tenants/create', 'TenantsController@store')->name('property.management.store.tenants');
        route::get('tenants/{tenant}/edit', 'TenantsController@edit')->name('property.management.edit.tenants');
        route::put('tenants/{tenant}/update', 'TenantsController@update')->name('property.management.update.tenants');
        route::delete('tenants/{tenant}/destroy', 'TenantsController@destroy')->name('property.management.tenants.destroy');
		
                    
        route::get('fixed-assets-names', 'FixedAssetController@index')->name('property.management.view.fixed.asset.names');
        route::get('fixed-assets-names/create', 'FixedAssetController@create')->name('property.management.create.fixed.asset.names');
        route::post('fixed-assets-names/create', 'FixedAssetController@store')->name('property.management.store.fixed.asset.names');
        route::get('fixed-assets-names/{fixedAssetName}/edit', 'FixedAssetController@edit')->name('property.management.edit.fixed.asset.names');
        route::put('fixed-assets-names/{fixedAssetName}/update', 'FixedAssetController@update')->name('property.management.update.fixed.asset.names');
        route::delete('fixed-assets-names/{fixedAssetName}/destroy', 'FixedAssetController@destroy')->name('property.management.destroy.fixed.asset.names');
                    
                    
        // route::post('consolidations', 'ConsolidationController@create')->name('property.management.view.consolidations');
        // route::post('consolidations', 'ConsolidationController@store')->name('property.management.store.consolidations');
                        
        // route::get('consolidation-income-statement/{consolidation}', 'ConsolidationIncomeStatementController@index')->name('view.property.management.consolidation.income.statement');
                         

        // route::get('microfinance-products/create', 'MicrofinanceProductsController@create')->name('create.microfinance.products');
        // route::post('microfinance-products/create', 'MicrofinanceProductsController@store')->name('store.microfinance.products');
                    
        // route::get('consumerfinance-products/create', 'ConsumerfinanceProductsController@create')->name('create.consumerfinance.products');
        // route::post('consumerfinance-products/create', 'ConsumerfinanceProductsController@store')->name('store.consumerfinance.products');
                    
        /**
         * * Start General Assumption
         */
        Route::group(['prefix'=>'study/{study}'], function () {
            /**
             * * General Assumption
             */
                        
            route::get('general-and-reserve-assumption', 'GeneralAndReservationAssumptionController@create')->name('property.management.create.general.assumption');
            route::get('general-and-reserve-assumption-old-data', 'GeneralAndReservationAssumptionController@getOldData');
            route::post('general-and-reserve-assumption', 'GeneralAndReservationAssumptionController@store')->name('property.management.store.general.assumption');
			
			
			route::get('occupied-properties-with-full-rent-coverage-duration', 'OccupiedPropertiesWithFullRentCoverageDurationController@create')->name('property.management.create.occupied.properties.with.full.rent.coverage.duration');
            route::get('occupied-properties-with-full-rent-coverage-duration-old-data', 'OccupiedPropertiesWithFullRentCoverageDurationController@getOldData');
            route::post('occupied-properties-with-full-rent-coverage-duration', 'OccupiedPropertiesWithFullRentCoverageDurationController@store')->name('property.management.store.occupied.properties.with.full.rent.coverage.duration');
			
			
			route::get('occupied-properties-with-partial-rent-coverage-duration', 'OccupiedPropertiesWithPartialRentCoverageDurationController@create')->name('property.management.create.occupied.properties.with.partial.rent.coverage.duration');
            route::get('occupied-properties-with-partial-rent-coverage-duration-old-data', 'OccupiedPropertiesWithPartialRentCoverageDurationController@getOldData');
            route::post('occupied-properties-with-partial-rent-coverage-duration', 'OccupiedPropertiesWithPartialRentCoverageDurationController@store')->name('property.management.store.occupied.properties.with.partial.rent.coverage.duration');
			
			
			route::get('properties-to-be-delivered', 'PropertiesToBeDeliveredController@create')->name('property.management.create.properties.to.be.delivered');
            route::get('properties-to-be-delivered-old-data', 'PropertiesToBeDeliveredController@getOldData');
            route::post('properties-to-be-delivered', 'PropertiesToBeDeliveredController@store')->name('property.management.store.properties.to.be.delivered');
			
            // route::get('microfinance-branches-assumption', 'MicrofinanceBranchAssumptionsController@create')->name('create.microfinance.branches.assumption');
            // route::post('microfinance-branches-assumption', 'MicrofinanceBranchAssumptionsController@store')->name('store.microfinance.branches.assumption');
                        
            /**
             * * End General Assumption
             */
         
                        
                         
      
          
                        
            route::get('dashboard', 'DashboardController@view')->name('property.management.view.results.dashboard');
            route::get('dashboard-with-sensitivity', 'CashInOutFlowController@view')->name('property.management.view.results.dashboard.with.sensitivity');
           
            Route::get('cash-in-out-flow', 'CashInOutFlowController@view')->name('property.management.cash.in.out.flow.result');
            Route::post('save-manual-equity-injection', 'CashInOutFlowController@saveManualEquityInjection')->name('property.management.save.manual.equity.injection');
            Route::get('balance-sheet', 'BalanceSheetController@view')->name('property.management.balance.sheet.result');
                         
            route::post('recalculate-spread-rates-sensitivity', 'RecalculateSpreadRateSensitivityController@recalculate')->name('property.management.calculate.spread.rate.sensitivity');
            route::get('income-statement', 'IncomeStatementController@index')->name('view.property.management.forecast.income.statement');
            route::get('income-statement-export', 'IncomeStatementController@exportReport')->name('export.property.management.forecast.income.statement');
            route::get('balance-sheet-export', 'BalanceSheetController@exportReport')->name('export.property.management.balance.sheet');
            route::get('cash-in-out-export', 'CashInOutFlowController@exportReport')->name('export.property.management.forecast.cash.in.out');
                            
                            
            // route::get('previous-years-income-statement', 'IncomeStatementController@viewPreviousTwoYearsIncomeStatement')->name('property.management.view.previous.property.management.forecast.income.statement');
            // route::post('previous-years-income-statement', 'IncomeStatementController@storePreviousTwoYearsIncomeStatement')->name('property.management.store.previous.property.management.forecast.income.statement');

                       
            route::get('valuation', 'ValuationController@index')->name('view.property.management.valuation');
            route::get('expense-statement-reports', 'ExpenseStatementReportController@index')->name('property.management.view.expense.statement.reports');
            route::post('expense-statement-reports', 'ExpenseStatementReportController@result')->name('property.management.result.expense.statement.reports');
                        
                        
          
            route::get('expenses', 'ExpensesController@create')->name('property.management.create.expenses');
            route::get('expenses-fetch-old-data', 'ExpensesController@expensesGetVueOldData');
            route::post('expenses', 'ExpensesController@store')->name('property.management.store.expenses');
			
			
			
			
			
            route::get('expense-name-from-category', 'ExpensesController@getExpenseNamesForCategory')->name('property.management.get.expense.name.for.category');
            route::get('expense-name-from-category-only-employees', 'ExpensesController@getExpenseNamesForCategoryOnlyEmployees')->name('property.management.get.expense.name.for.category.only.in.employee');
            route::get('expense-name-from-category-only-branch', 'ExpensesController@getExpenseNamesForCategoryOnlyBranches')->name('property.management.get.expense.name.for.category.only.in.branch');
            route::get('fixed-assets/ffe', 'FixedAssetsController@create')->name('property.management.create.ffe.fixed.assets');
            route::get('fixed-assets-old-data', 'FixedAssetsController@getOldData');
            route::post('fixed-assets/ffe', 'FixedAssetsController@store')->name('property.management.store.ffe.fixed.assets');
			
			route::get('forecasted-properties', 'ForecastedPropertiesController@create')->name('property.management.create.forecasted.properties');
            route::get('forecasted-properties-fetch-old-data', 'ForecastedPropertiesController@getOldData');
            route::post('forecasted-properties', 'ForecastedPropertiesController@store')->name('property.management.store.forecasted.properties');
			
            // route::get('fixed-assets/ffe/funding-structure', 'FixedAssetsController@createFundingStructure')->name('create.ffe.funding.structure.fixed.assets');
            // route::post('fixed-assets/ffe/funding-structure', 'FixedAssetsController@storeFunding')->name('store.ffe.funding.structure.fixed.assets');
                        
            // route::get('fixed-assets/new-branches', 'NewBranchFixedAssetsController@create')->name('create.new.branch.fixed.assets');
                        
            // route::post('fixed-assets/new-branches', 'NewBranchFixedAssetsController@store')->name('store.new.branch.fixed.assets');
                        
            // route::get('fixed-assets/per-employee', 'PerEmployeeFixedAssetsController@create')->name('create.per.employee.fixed.assets');
            // route::post('fixed-assets/per-employee', 'PerEmployeeFixedAssetsController@store')->name('store.per.employee.fixed.assets');
                        
            // route::get('fixed-assets/employee', 'EmployeeFixedAssetsController@create')->name('create.per.employee.fixed.assets');
            // route::post('fixed-assets/employee', 'EmployeeFixedAssetsController@store')->name('store.per.employee.fixed.assets');
            // route::post('fixed-assets/per-employee/funding-structure', 'NewBranchFixedAssetsController@storeFunding')->name('store.per.employee.funding.structure.fixed.assets');
                        
                        
            route::post('departments', 'ManpowerExpensesController@storeDepartmentPositions')->name('store.department.positions.for.property.management');
            route::get('manpower', 'ManpowerExpensesController@create')->name('view.manpower.for.property.management');
            route::post('manpower', 'ManpowerExpensesController@store')->name('store.manpower.for.property.management');
            route::get('manpower-expenses-fetch-old-data', 'ManpowerExpensesController@getOldData');

                        
            route::get('opening-balances', 'OpeningBalancesController@create')->name('view.opening.balances.for.property.management');
            route::post('opening-balances', 'OpeningBalancesController@store')->name('store.opening.balances.for.property.management');
                        
                        
            // route::get('delete/{position}/manpower','ManpowerExpensesController@deleteSinglePosition')->name('delete.single.position.for.property.management');
            // route::get('delete-department/{department}/manpower','ManpowerExpensesController@deleteSingleDepartment')->name('delete.single.department.for.property.management');
            // route::get('get-positions-based-on-department', 'ManpowerExpensesController@getPositionsBasedOnDepartment'); // ajax ;
            // route::get('get-stream-category-based-on-revenue-stream-id', 'AjaxController@getStreamCategoryBasedOnRevenueStream');
                        
            // Route::post('get-stream-category-based-on-revenue-stream','AjaxController@getStreamCategoryBasedOnRevenueStream');
            // Route::get('get-positions-based-on-departments', 'AjaxController@getPositionsBasedOnDepartments');
                        
            /**
             * * End expenses table
             */
                        
                        
        });
        /**
         * * Study Info
        */
                    
                        
                         
                         
    });
                    
                    
});
