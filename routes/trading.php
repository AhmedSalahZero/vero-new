<?php
Route::group(['prefix'=>TRADING_URL_PREFIX,'middleware'=>'isTrading'], function () {
    Route::get('fixed-payments-at-end/{study}', 'Loans2Controller@create')->name('trading.fixed.loan.fixed.at.end');
    Route::get('fixed-payments-at-beginning/{study}', 'Loans2Controller@create')->name('trading.fixed.loan.fixed.at.beginning');
    Route::get('calculate-loan-amount/{study}', 'Loans2Controller@create')->name('trading.calc.loan.amount');
    Route::get('calculate-interest-rate/{study}', 'Loans2Controller@create')->name('trading.calc.interest.percentage');
    Route::get('variable-payments/{study}', 'Loans2Controller@create')->name('trading.variable.payments');
    Route::group(['namespace'=>'Tradings'], function () {
		
        route::get('study', 'StudyController@index')->name('trading.view.study');
        route::get('study/create', 'StudyController@create')->name('trading.create.study');
        Route::get('study/{study}/edit', 'StudyController@edit')->name('trading.edit.study');
        Route::put('study/{study}/update', 'StudyController@update')->name('trading.update.study');
        route::post('study', 'StudyController@store')->name('store.trading.services');
        route::delete('study/{study}/delete', 'StudyController@destroy')->name('trading.study.destroy');
        Route::post('/copy/{study}', 'CopyStudyController@index')->name('trading.copy.study');
		Route::get('property-dashboard', 'DashboardController@viewPropertyDashboard')->name('trading.view.trading.dashboard');            
		Route::get('property-cashflow-forecast-dashboard', 'DashboardController@viewTradingCashflowForecastDashboard')->name('trading.view.property.cashflow.forecast.dashboard');   
		Route::get('dashboard/cashflow-forecast-old-data', 'DashboardController@getCashflowForecastDashboardOldData');
		Route::post('dashboard/cashflow-forecast-submit', 'DashboardController@submitCashflowForecast')->name('trading.dashboard.cashflow-forecast.submit');
        route::get('tradings', 'TradingsController@index')->name('trading.view.tradings');
        route::get('tradings/create', 'TradingsController@create')->name('trading.create.tradings');
        route::get('tradings/{property}/edit', 'TradingsController@edit')->name('trading.edit.tradings');
        route::get('tradings/tradings-old-data', 'TradingsController@getOldData');
        route::post('tradings/create', 'TradingsController@store')->name('trading.store.tradings');
        route::put('tradings/{property}/update', 'TradingsController@update')->name('trading.update.tradings');
        route::delete('tradings/{property}/destroy', 'TradingsController@destroy')->name('trading.destroy.tradings');
		route::post('tradings/{property}/due-installments', 'TradingsController@storeDueInstallments')->name('property-managements.tradings.due-installments.store');
        
		// route::get('tradings/{property}/property-expenses', 'PropertyExpensesController@create')->name('trading.create.property.expenses');
		// route::get('tradings/{property}/property-expenses-old-data', 'PropertyExpensesController@getOldData');
		// route::post('tradings/{property}/property-expenses', 'PropertyExpensesController@store')->name('trading.store.property.expenses');
		// route::delete('tradings/{property}/property-expenses/{propertyExpense}/destroy', 'PropertyExpensesController@destroy')->name('trading.destroy.property.expenses');
		
        // Contracts routes
        // route::get('tradings/{property}/contracts', 'ContractsController@index')->name('property-managements.tradings.contracts.index');
        // route::get('tradings/{property}/contracts/get', 'ContractsController@getContracts')->name('property-managements.tradings.contracts.get');
        // route::get('tradings/{property}/contracts/create', 'ContractsController@create')->name('property-managements.tradings.contracts.create');
        // route::get('tradings/{property}/contracts/old-data', 'ContractsController@getOldData')->name('property-managements.tradings.contracts.old-data');
        // route::post('tradings/{property}/contracts/store', 'ContractsController@store')->name('property-managements.tradings.contracts.store');
        // route::get('tradings/{property}/contracts/{contract}/edit', 'ContractsController@edit')->name('property-managements.tradings.contracts.edit');
        // route::put('tradings/{property}/contracts/{contract}/update', 'ContractsController@update')->name('property-managements.tradings.contracts.update');

        // route::delete('tradings/{property}/contracts/{contract}/destroy', 'ContractsController@destroy')->name('property-managements.tradings.contracts.destroy');
        // route::post('tradings/{property}/contracts/{contract}/mark-as-finished', 'ContractsController@markAsFinished')->name('property-managements.tradings.contracts.mark-as-finished');
        // route::get('tradings/{property}/contracts/{contract}/renew-form', 'ContractsController@showRenewForm')->name('property-managements.tradings.contracts.renew-form');
        // route::post('tradings/{property}/contracts/{contract}/renew', 'ContractsController@renew')->name('property-managements.tradings.contracts.renew');
		
		
	
		
		
		Route::get('foreign-exchange-rate', 'ForeignExchangeRateController@index')->name('view.trading.foreign.exchange.rate');
		Route::post('foreign-exchange-rate', 'ForeignExchangeRateController@store')->name('store.trading.foreign.exchange.rate');
		Route::get('foreign-exchange-rate/edit/{foreignExchangeRate}', 'ForeignExchangeRateController@edit')->name('edit.trading.foreign.exchange.rate');
		Route::patch('foreign-exchange-rate/edit/{foreignExchangeRate}', 'ForeignExchangeRateController@update')->name('update.trading.foreign.exchange.rate');
		Route::delete('delete-foreign-exchange-rate/edit/{foreignExchangeRate}', 'ForeignExchangeRateController@destroy')->name('delete.trading.foreign.exchange.rate');

		
                    
                    
        // route::get('existing-branches/create', 'ExistingBranchesController@create')->name('create.existing.branches');
        // route::post('existing-branches/create', 'ExistingBranchesController@store')->name('store.existing.branches');
                    
                    
        route::get('departments', 'DepartmentController@index')->name('trading.view.departments');
        route::get('departments/create/{type}', 'DepartmentController@create')->name('trading.create.departments');
        route::post('departments/create/{type}', 'DepartmentController@store')->name('trading.store.departments');
        route::get('departments/{department}/edit/{type}', 'DepartmentController@edit')->name('trading.edit.departments');
        route::put('departments/{department}/update/{type}', 'DepartmentController@update')->name('trading.update.departments');
        route::delete('departments/{department}/destroy', 'DepartmentController@destroy')->name('trading.departments.destroy');
                    
      
        route::get('expense-names', 'ExpenseController@index')->name('trading.view.expense.names');
        route::get('expense-names/create', 'ExpenseController@create')->name('trading.create.expense.names');
        route::post('expense-names/create', 'ExpenseController@store')->name('trading.store.expense.names');
        route::get('expense-names/{expenseType}/edit', 'ExpenseController@edit')->name('trading.edit.expense.names');
        route::put('expense-names/{expenseType}/update', 'ExpenseController@update')->name('trading.update.expense.names');
        route::delete('expense-names/{expenseType}/destroy', 'ExpenseController@destroy')->name('trading.expense.names.destroy');
                  
		
		route::get('tenants', 'TenantsController@index')->name('trading.view.tenants');
        route::get('tenants/create', 'TenantsController@create')->name('trading.create.tenants');
        route::post('tenants/create', 'TenantsController@store')->name('trading.store.tenants');
        route::get('tenants/{tenant}/edit', 'TenantsController@edit')->name('trading.edit.tenants');
        route::put('tenants/{tenant}/update', 'TenantsController@update')->name('trading.update.tenants');
        route::delete('tenants/{tenant}/destroy', 'TenantsController@destroy')->name('trading.tenants.destroy');
		
                    
        route::get('fixed-assets-names', 'FixedAssetController@index')->name('trading.view.fixed.asset.names');
        route::get('fixed-assets-names/create', 'FixedAssetController@create')->name('trading.create.fixed.asset.names');
        route::post('fixed-assets-names/create', 'FixedAssetController@store')->name('trading.store.fixed.asset.names');
        route::get('fixed-assets-names/{fixedAssetName}/edit', 'FixedAssetController@edit')->name('trading.edit.fixed.asset.names');
        route::put('fixed-assets-names/{fixedAssetName}/update', 'FixedAssetController@update')->name('trading.update.fixed.asset.names');
        route::delete('fixed-assets-names/{fixedAssetName}/destroy', 'FixedAssetController@destroy')->name('trading.destroy.fixed.asset.names');
                    
                    
        // route::post('consolidations', 'ConsolidationController@create')->name('trading.view.consolidations');
        // route::post('consolidations', 'ConsolidationController@store')->name('trading.store.consolidations');
                        
        // route::get('consolidation-income-statement/{consolidation}', 'ConsolidationIncomeStatementController@index')->name('view.trading.consolidation.income.statement');
                         

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
                        
            route::get('general-and-reserve-assumption', 'GeneralAndReservationAssumptionController@create')->name('trading.create.general.assumption');
            route::get('general-and-reserve-assumption-old-data', 'GeneralAndReservationAssumptionController@getOldData');
            route::post('general-and-reserve-assumption', 'GeneralAndReservationAssumptionController@store')->name('trading.store.general.assumption');
			
			
			route::get('occupied-tradings-with-full-rent-coverage-duration', 'OccupiedTradingsWithFullRentCoverageDurationController@create')->name('trading.create.occupied.tradings.with.full.rent.coverage.duration');
            route::get('occupied-tradings-with-full-rent-coverage-duration-old-data', 'OccupiedTradingsWithFullRentCoverageDurationController@getOldData');
            route::post('occupied-tradings-with-full-rent-coverage-duration', 'OccupiedTradingsWithFullRentCoverageDurationController@store')->name('trading.store.occupied.tradings.with.full.rent.coverage.duration');
			
			
			route::get('occupied-tradings-with-partial-rent-coverage-duration', 'OccupiedTradingsWithPartialRentCoverageDurationController@create')->name('trading.create.occupied.tradings.with.partial.rent.coverage.duration');
            route::get('occupied-tradings-with-partial-rent-coverage-duration-old-data', 'OccupiedTradingsWithPartialRentCoverageDurationController@getOldData');
            route::post('occupied-tradings-with-partial-rent-coverage-duration', 'OccupiedTradingsWithPartialRentCoverageDurationController@store')->name('trading.store.occupied.tradings.with.partial.rent.coverage.duration');
			
			
			route::get('tradings-to-be-delivered', 'TradingsToBeDeliveredController@create')->name('trading.create.tradings.to.be.delivered');
            route::get('tradings-to-be-delivered-old-data', 'TradingsToBeDeliveredController@getOldData');
            route::post('tradings-to-be-delivered', 'TradingsToBeDeliveredController@store')->name('trading.store.tradings.to.be.delivered');
			
            // route::get('microfinance-branches-assumption', 'MicrofinanceBranchAssumptionsController@create')->name('create.microfinance.branches.assumption');
            // route::post('microfinance-branches-assumption', 'MicrofinanceBranchAssumptionsController@store')->name('store.microfinance.branches.assumption');
                        
            /**
             * * End General Assumption
             */
         
                        
                         
      
          
                        
            route::get('dashboard', 'DashboardController@view')->name('trading.view.results.dashboard');
            route::get('dashboard-with-sensitivity', 'CashInOutFlowController@view')->name('trading.view.results.dashboard.with.sensitivity');
           
            Route::get('cash-in-out-flow', 'CashInOutFlowController@view')->name('trading.cash.in.out.flow.result');
            Route::post('save-manual-equity-injection', 'CashInOutFlowController@saveManualEquityInjection')->name('trading.save.manual.equity.injection');
            Route::get('balance-sheet', 'BalanceSheetController@view')->name('trading.balance.sheet.result');
                         
            route::post('recalculate-spread-rates-sensitivity', 'RecalculateSpreadRateSensitivityController@recalculate')->name('trading.calculate.spread.rate.sensitivity');
            route::get('income-statement', 'IncomeStatementController@index')->name('view.trading.forecast.income.statement');
            route::get('income-statement-export', 'IncomeStatementController@exportReport')->name('export.trading.forecast.income.statement');
            route::get('balance-sheet-export', 'BalanceSheetController@exportReport')->name('export.trading.balance.sheet');
            route::get('cash-in-out-export', 'CashInOutFlowController@exportReport')->name('export.trading.forecast.cash.in.out');
                            
                            
            route::get('previous-years-income-statement', 'IncomeStatementController@viewPreviousTwoYearsIncomeStatement')->name('trading.view.previous.trading.forecast.income.statement');
            route::post('previous-years-income-statement', 'IncomeStatementController@storePreviousTwoYearsIncomeStatement')->name('trading.store.previous.trading.forecast.income.statement');

                       
            route::get('valuation', 'ValuationController@index')->name('view.trading.valuation');
            route::get('expense-statement-reports', 'ExpenseStatementReportController@index')->name('trading.view.expense.statement.reports');
            route::post('expense-statement-reports', 'ExpenseStatementReportController@result')->name('trading.result.expense.statement.reports');
                        
                        
          
            route::get('expenses', 'ExpensesController@create')->name('trading.create.expenses');
            route::get('expenses-fetch-old-data', 'ExpensesController@expensesGetVueOldData');
            route::post('expenses', 'ExpensesController@store')->name('trading.store.expenses');
			
			
			
			
			
            route::get('expense-name-from-category', 'ExpensesController@getExpenseNamesForCategory')->name('trading.get.expense.name.for.category');
            route::get('expense-name-from-category-only-employees', 'ExpensesController@getExpenseNamesForCategoryOnlyEmployees')->name('trading.get.expense.name.for.category.only.in.employee');
            route::get('expense-name-from-category-only-branch', 'ExpensesController@getExpenseNamesForCategoryOnlyBranches')->name('trading.get.expense.name.for.category.only.in.branch');
            route::get('fixed-assets/ffe', 'FixedAssetsController@create')->name('trading.create.ffe.fixed.assets');
            route::get('fixed-assets-old-data', 'FixedAssetsController@getOldData');
            route::post('fixed-assets/ffe', 'FixedAssetsController@store')->name('trading.store.ffe.fixed.assets');
			
			route::get('forecasted-tradings', 'ForecastedTradingsController@create')->name('trading.create.forecasted.tradings');
            route::get('forecasted-tradings-fetch-old-data', 'ForecastedTradingsController@getOldData');
            route::post('forecasted-tradings', 'ForecastedTradingsController@store')->name('trading.store.forecasted.tradings');
			
            // route::get('fixed-assets/ffe/funding-structure', 'FixedAssetsController@createFundingStructure')->name('create.ffe.funding.structure.fixed.assets');
            // route::post('fixed-assets/ffe/funding-structure', 'FixedAssetsController@storeFunding')->name('store.ffe.funding.structure.fixed.assets');
                        
            // route::get('fixed-assets/new-branches', 'NewBranchFixedAssetsController@create')->name('create.new.branch.fixed.assets');
                        
            // route::post('fixed-assets/new-branches', 'NewBranchFixedAssetsController@store')->name('store.new.branch.fixed.assets');
                        
            // route::get('fixed-assets/per-employee', 'PerEmployeeFixedAssetsController@create')->name('create.per.employee.fixed.assets');
            // route::post('fixed-assets/per-employee', 'PerEmployeeFixedAssetsController@store')->name('store.per.employee.fixed.assets');
                        
            // route::get('fixed-assets/employee', 'EmployeeFixedAssetsController@create')->name('create.per.employee.fixed.assets');
            // route::post('fixed-assets/employee', 'EmployeeFixedAssetsController@store')->name('store.per.employee.fixed.assets');
            // route::post('fixed-assets/per-employee/funding-structure', 'NewBranchFixedAssetsController@storeFunding')->name('store.per.employee.funding.structure.fixed.assets');
                        
                        
            route::post('departments', 'ManpowerExpensesController@storeDepartmentPositions')->name('store.department.positions.for.trading');
            route::get('manpower', 'ManpowerExpensesController@create')->name('view.manpower.for.trading');
            route::post('manpower', 'ManpowerExpensesController@store')->name('store.manpower.for.trading');
            route::get('manpower-expenses-fetch-old-data', 'ManpowerExpensesController@getOldData');

                        
            route::get('opening-balances', 'OpeningBalancesController@create')->name('view.opening.balances.for.trading');
            route::post('opening-balances', 'OpeningBalancesController@store')->name('store.opening.balances.for.trading');
                        
                        
            // route::get('delete/{position}/manpower','ManpowerExpensesController@deleteSinglePosition')->name('delete.single.position.for.trading');
            // route::get('delete-department/{department}/manpower','ManpowerExpensesController@deleteSingleDepartment')->name('delete.single.department.for.trading');
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
