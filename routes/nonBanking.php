<?php
Route::group(['prefix'=>NON_BANKING_SERVICE_URL_PREFIX,'middleware'=>'isNonBankingService'], function () {
    Route::get('fixed-payments-at-end/{study}', 'Loans2Controller@create')->name('non.banking.fixed.loan.fixed.at.end');
    Route::get('fixed-payments-at-beginning/{study}', 'Loans2Controller@create')->name('non.banking.fixed.loan.fixed.at.beginning');
    Route::get('calculate-loan-amount/{study}', 'Loans2Controller@create')->name('non.banking.calc.loan.amount');
    Route::get('calculate-interest-rate/{study}', 'Loans2Controller@create')->name('non.banking.calc.interest.percentage');
    Route::get('variable-payments/{study}', 'Loans2Controller@create')->name('non.banking.variable.payments');
    Route::group(['namespace'=>'NonBankingServices'], function () {
                        
        route::get('study', 'StudyController@index')->name('non.banking.view.study');
        route::get('study/create', 'StudyController@create')->name('non.banking.create.study');
        Route::get('study/{study}/edit', 'StudyController@edit')->name('non.banking.edit.study');
        Route::put('study/{study}/update', 'StudyController@update')->name('non.banking.update.study');
        route::post('study', 'StudyController@store')->name('store.non.banking.services');
        route::delete('study/{study}/delete', 'StudyController@destroy')->name('non.banking.study.destroy');
        Route::post('/copy/{study}', 'CopyStudyController@index')->name('non.banking.copy.study');
                    
        // route::get('leasing-categories','LeasingCategoriesController@index')->name('view.leasing.categories');
        route::get('leasing-products/create', 'LeasingCategoriesController@create')->name('create.leasing.categories');
        route::post('leasing-products/create', 'LeasingCategoriesController@store')->name('store.leasing.categories');
                    
                    
        route::get('existing-branches/create', 'ExistingBranchesController@create')->name('create.existing.branches');
        route::post('existing-branches/create', 'ExistingBranchesController@store')->name('store.existing.branches');
                    
                    
        route::get('departments', 'DepartmentController@index')->name('non.banking.view.departments');
        route::get('departments/create/{type}', 'DepartmentController@create')->name('non.banking.create.departments');
        route::post('departments/create/{type}', 'DepartmentController@store')->name('non.banking.store.departments');
        route::get('departments/{department}/edit/{type}', 'DepartmentController@edit')->name('non.banking.edit.departments');
        route::put('departments/{department}/update/{type}', 'DepartmentController@update')->name('non.banking.update.departments');
        route::delete('departments/{department}/destroy', 'DepartmentController@destroy')->name('non.banking.departments.destroy');
                    
        // route::get('microfinance-departments/create','MicrofinanceDepartmentController@create')->name('create.microfinance-departments');
        // route::post('microfinance-departments/create', 'MicrofinanceDepartmentController@store')->name('store.microfinance-departments');
        // route::get('microfinance-departments/{microfinanceDepartment}/edit', 'MicrofinanceDepartmentController@edit')->name('edit.microfinance-departments');
        // route::put('microfinance-departments/{microfinanceDepartment}/update', 'MicrofinanceDepartmentController@update')->name('update.microfinance-departments');
                    
        route::get('expense-names', 'ExpenseController@index')->name('non.banking.view.expense.names');
        route::get('expense-names/create', 'ExpenseController@create')->name('non.banking.create.expense.names');
        route::post('expense-names/create', 'ExpenseController@store')->name('non.banking.store.expense.names');
        route::get('expense-names/{expenseType}/edit', 'ExpenseController@edit')->name('non.banking.edit.expense.names');
        route::put('expense-names/{expenseType}/update', 'ExpenseController@update')->name('non.banking.update.expense.names');
        route::delete('expense-names/{expenseType}/destroy', 'ExpenseController@destroy')->name('non.banking.expense.names.destroy');
                    
                    
        route::get('fixed-assets-names', 'FixedAssetController@index')->name('non.banking.view.fixed.asset.names');
        route::get('fixed-assets-names/create', 'FixedAssetController@create')->name('non.banking.create.fixed.asset.names');
        route::post('fixed-assets-names/create', 'FixedAssetController@store')->name('non.banking.store.fixed.asset.names');
        route::get('fixed-assets-names/{fixedAssetName}/edit', 'FixedAssetController@edit')->name('non.banking.edit.fixed.asset.names');
        route::put('fixed-assets-names/{fixedAssetName}/update', 'FixedAssetController@update')->name('non.banking.update.fixed.asset.names');
        route::delete('fixed-assets-names/{fixedAssetName}/destroy', 'FixedAssetController@destroy')->name('non.banking.destroy.fixed.asset.names');
                    
                    
        route::post('consolidations', 'ConsolidationController@create')->name('non.banking.view.consolidations');
        route::post('consolidations', 'ConsolidationController@store')->name('non.banking.store.consolidations');
                        
        route::get('consolidation-income-statement/{consolidation}', 'ConsolidationIncomeStatementController@index')->name('view.non.banking.consolidation.income.statement');
                         
      
        route::get('microfinance-products/create', 'MicrofinanceProductsController@create')->name('create.microfinance.products');
        route::post('microfinance-products/create', 'MicrofinanceProductsController@store')->name('store.microfinance.products');
                    
        route::get('consumerfinance-products/create', 'ConsumerfinanceProductsController@create')->name('create.consumerfinance.products');
        route::post('consumerfinance-products/create', 'ConsumerfinanceProductsController@store')->name('store.consumerfinance.products');
                    
        /**
         * * Start General Assumption
         */
        Route::group(['prefix'=>'study/{study}'], function () {
            /**
             * * General Assumption
             */
                        
            route::get('general-and-reserve-assumption', 'GeneralAndReservationAssumptionController@create')->name('non.banking.create.general.assumption');
            route::get('general-and-reserve-assumption-old-data', 'GeneralAndReservationAssumptionController@getOldData');
            route::post('general-and-reserve-assumption', 'GeneralAndReservationAssumptionController@store')->name('non.banking.store.general.assumption');
                        
                      
            /**
             * * End General Assumption
             */
                    
            /**
             * * Start Leasing Revenue Streams Breakdown
             */
            route::get('revenue-streams-breakdown/leasing', 'LeasingController@create')->name('create.leasing.revenue.stream.breakdown');
            route::get('leasing-fetch-old-data', 'LeasingController@getOldData');
            route::post('revenue-streams-breakdown/leasing', 'LeasingController@store')->name('store.leasing.revenue.stream.breakdown');
            /**
             * * End Leasing Revenue Streams Breakdown
             */
                        
                         
            /**
            * * Start Direct Factoring Revenue Streams Breakdown
            */
            route::get('revenue-streams-breakdown/direct-factoring', 'DirectFactoringController@create')->name('create.direct.factoring.revenue.stream.breakdown');
            route::post('revenue-streams-breakdown/direct-factoring', 'DirectFactoringController@store')->name('store.direct.factoring.revenue.stream.breakdown');
            route::get('direct-factoring-fetch-old-data', 'DirectFactoringController@getOldData');
                   
			/**
			* * Start Spreadsheet
			*/
			route::get('spreadsheet', 'SpreadsheetController@create')->name('create.spreadsheet');
			route::post('spreadsheet', 'SpreadsheetController@store')->name('store.spreadsheet');
			route::get('spreadsheet-fetch-old-data', 'SpreadsheetController@getOldData');
			/**
			* * End Spreadsheet
			*/
			
            /**
             * * End Direct Factoring Revenue Streams Breakdown
             */
                        
                         
                         
            /**
             * * Start Reverse Factoring Revenue Streams Breakdown
             */
            route::get('revenue-streams-breakdown/reverse-factoring', 'ReverseFactoringController@create')->name('create.reverse.factoring.revenue.stream.breakdown');
            route::post('revenue-streams-breakdown/reverse-factoring', 'ReverseFactoringController@store')->name('store.reverse.factoring.revenue.stream.breakdown');
            route::get('reverse-factoring-fetch-old-data', 'ReverseFactoringController@getOldData');
            /**
             * * End Reverse Factoring Revenue Streams Breakdown
             */
                        
                         
            /**
             * * Start Ijara Mortgage Revenue Streams Breakdown
             */
            route::get('revenue-streams-breakdown/ijara', 'IjaraMortgageController@create')->name('create.ijara.mortgage.revenue.stream.breakdown');
            route::post('revenue-streams-breakdown/ijara', 'IjaraMortgageController@store')->name('store.ijara.mortgage.revenue.stream.breakdown');
            route::get('ijara-mortgage-fetch-old-data', 'IjaraMortgageController@getOldData');
            route::get('securitization', 'SecuritizationController@create')->name('create.securitization');
            route::post('securitization', 'SecuritizationController@store')->name('store.securitization');
                        
                        
            route::get('consumer-finance', 'ConsumerFinanceController@create')->name('create.consumer.finance');
            route::post('consumer-finance', 'ConsumerFinanceController@store')->name('store.consumer.finance');
                            
            route::get('microfinance/all-branches/{branch_id?}', 'AllBranchesMicrofinanceControllerController@create')->name('create.all-branches.microfinance');
            route::post('microfinance/all-branches/{branch_id?}', 'AllBranchesMicrofinanceControllerController@store')->name('store.all-branches.microfinance');
                        
            route::get('get-decrease-rate-based-on-flat-rate', 'AllBranchesMicrofinanceControllerController@getDecreaseRateBasedOnFlatRate'); // ajax ;

                        
            // route::get('microfinance/by-branches', 'ByBranchesMicrofinanceControllerController@create')->name('create.by-branches.microfinance');
            // route::post('microfinance/by-branches', 'ByBranchesMicrofinanceControllerController@store')->name('store.by-branches.microfinance');
                        
            route::get('microfinance/planning-by-branch', 'ByBranchesMicrofinanceControllerController@create')->name('create.by-branch.microfinance');
            // route::post('microfinance/allocate-by-branch', 'ByBranchesMicrofinanceControllerController@store')->name('store.by-branch.microfinance');
                        
                        
            route::get('microfinance/new-branches', 'NewBranchesMicrofinanceController@create')->name('create.new-branches.microfinance');
            route::post('microfinance/new-branches', 'NewBranchesMicrofinanceController@store')->name('store.new-branches.microfinance');
                        
            route::get('microfinance/loans', 'MicrofinanceLoanController@create')->name('create.loan.microfinance');
            route::post('microfinance/loans', 'MicrofinanceLoanController@store')->name('store.loan.microfinance');
            route::get('microfinance/loan-report/{branchId}', 'MicrofinanceLoanReportController@create')->name('view.loan.report.microfinance');
                        
                        
            route::get('microfinance-products-mix', 'MicrofinanceProductMixControllerController@create')->name('create.microfinance.product.mix');
            route::post('microfinance-products-mix', 'MicrofinanceProductMixControllerController@store')->name('store.microfinance.product.mix');
                        
            // route::get('revenue-streams-breakdown/microfinance', 'MicrofinanceRevenueStreamBreakdownController@create')->name('create.microfinance.revenue.stream.breakdown');
            // route::post('revenue-streams-breakdown/microfinance', 'MicrofinanceRevenueStreamBreakdownController@store')->name('store.microfinance.revenue.stream.breakdown');
                        
            /**
             * * End Ijara Mortgage Revenue Streams Breakdown
             */
                        
            /**
            * * Start Portfolio Mortgage Revenue Streams Breakdown
            */
            route::get('revenue-streams-breakdown/portfolio-mortgage', 'PortfolioMortgageController@create')->name('create.portfolio.mortgage.revenue.stream.breakdown');
            route::post('revenue-streams-breakdown/portfolio-mortgage', 'PortfolioMortgageController@store')->name('store.portfolio.mortgage.revenue.stream.breakdown');
            // Route::get('add-new-portfolio-mortgage-category', 'PortfolioMortgageController@addNewCategory')->name('add.new.portfolio.mortgage.category');
            // Route::get('delete-portfolio-mortgage-category/{portfolioMortgageCategory}', 'PortfolioMortgageController@deleteCategory')->name('delete.portfolio.mortgage.category');
            route::get('portfolio-mortgage-fetch-old-data', 'PortfolioMortgageController@getOldData');
            /**
             * * End Portfolio Mortgage Revenue Streams Breakdown
             */
                        
            route::get('dashboard', 'DashboardController@view')->name('non.banking.view.results.dashboard');
            route::get('dashboard-with-sensitivity', 'CashInOutFlowController@view')->name('non.banking.view.results.dashboard.with.sensitivity');
                         
            Route::get('cash-in-out-flow', 'CashInOutFlowController@view')->name('non.banking.cash.in.out.flow.result');
            Route::post('save-manual-equity-injection', 'CashInOutFlowController@saveManualEquityInjection')->name('non.banking.save.manual.equity.injection');
            Route::get('balance-sheet', 'BalanceSheetController@view')->name('non.banking.balance.sheet.result');
                         
            route::post('recalculate-spread-rates-sensitivity', 'RecalculateSpreadRateSensitivityController@recalculate')->name('non.banking.calculate.spread.rate.sensitivity');
            route::get('income-statement', 'IncomeStatementController@index')->name('view.non.banking.forecast.income.statement');
            route::get('income-statement-export', 'IncomeStatementController@exportReport')->name('export.non.banking.forecast.income.statement');
            route::get('balance-sheet-export', 'BalanceSheetController@exportReport')->name('export.non.banking.balance.sheet');
            route::get('cash-in-out-export', 'CashInOutFlowController@exportReport')->name('export.non.banking.forecast.cash.in.out');
                            
                            
            route::get('previous-years-income-statement', 'IncomeStatementController@viewPreviousTwoYearsIncomeStatement')->name('non.banking.view.previous.non.banking.forecast.income.statement');
            route::post('previous-years-income-statement', 'IncomeStatementController@storePreviousTwoYearsIncomeStatement')->name('non.banking.store.previous.non.banking.forecast.income.statement');

                       
            route::get('valuation', 'ValuationController@index')->name('view.non.banking.valuation');
            route::get('expense-statement-reports', 'ExpenseStatementReportController@index')->name('non.banking.view.expense.statement.reports');
            route::post('expense-statement-reports', 'ExpenseStatementReportController@result')->name('non.banking.result.expense.statement.reports');
                        
                        
            
            /**
             * * Non Banking Expenses
             */
            route::get('expenses', 'ExpensesController@create')->name('non.banking.create.expenses');
            route::get('expenses-fetch-old-data', 'ExpensesController@expensesGetVueOldData');
            route::post('expenses', 'ExpensesController@store')->name('non.banking.store.expenses');
            route::get('expense-name-from-category', 'ExpensesController@getExpenseNamesForCategory')->name('non.banking.get.expense.name.for.category');
            route::get('expense-name-from-category-only-employees', 'ExpensesController@getExpenseNamesForCategoryOnlyEmployees')->name('non.banking.get.expense.name.for.category.only.in.employee');
            route::get('expense-name-from-category-only-branch', 'ExpensesController@getExpenseNamesForCategoryOnlyBranches')->name('non.banking.get.expense.name.for.category.only.in.branch');
            route::get('fixed-assets/ffe', 'FixedAssetsController@create')->name('non.banking.create.ffe.fixed.assets');
            route::get('fixed-assets-old-data', 'FixedAssetsController@getOldData');
            route::post('fixed-assets/ffe', 'FixedAssetsController@store')->name('non.banking.store.ffe.fixed.assets');
            // route::get('fixed-assets/ffe/funding-structure', 'FixedAssetsController@createFundingStructure')->name('create.ffe.funding.structure.fixed.assets');
            // route::post('fixed-assets/ffe/funding-structure', 'FixedAssetsController@storeFunding')->name('store.ffe.funding.structure.fixed.assets');
                        
            // route::get('fixed-assets/new-branches', 'NewBranchFixedAssetsController@create')->name('create.new.branch.fixed.assets');
                        
            // route::post('fixed-assets/new-branches', 'NewBranchFixedAssetsController@store')->name('store.new.branch.fixed.assets');
                        
            // route::get('fixed-assets/per-employee', 'PerEmployeeFixedAssetsController@create')->name('create.per.employee.fixed.assets');
            // route::post('fixed-assets/per-employee', 'PerEmployeeFixedAssetsController@store')->name('store.per.employee.fixed.assets');
                        
            // route::get('fixed-assets/employee', 'EmployeeFixedAssetsController@create')->name('create.per.employee.fixed.assets');
            // route::post('fixed-assets/employee', 'EmployeeFixedAssetsController@store')->name('store.per.employee.fixed.assets');
            // route::post('fixed-assets/per-employee/funding-structure', 'NewBranchFixedAssetsController@storeFunding')->name('store.per.employee.funding.structure.fixed.assets');
                        
                        
            route::post('departments', 'ManpowerExpensesController@storeDepartmentPositions')->name('store.department.positions.for.non.banking');
            route::get('manpower', 'ManpowerExpensesController@create')->name('view.manpower.for.non.banking');
            route::post('manpower', 'ManpowerExpensesController@store')->name('store.manpower.for.non.banking');
            route::get('manpower-expenses-fetch-old-data', 'ManpowerExpensesController@getOldData');

                        
            route::get('opening-balances', 'OpeningBalancesController@create')->name('view.opening.balances.for.non.banking');
            route::post('opening-balances', 'OpeningBalancesController@store')->name('store.opening.balances.for.non.banking');
                        
                        
            // route::get('delete/{position}/manpower','ManpowerExpensesController@deleteSinglePosition')->name('delete.single.position.for.non.banking');
            // route::get('delete-department/{department}/manpower','ManpowerExpensesController@deleteSingleDepartment')->name('delete.single.department.for.non.banking');
            route::get('get-positions-based-on-department', 'ManpowerExpensesController@getPositionsBasedOnDepartment'); // ajax ;
            route::get('get-stream-category-based-on-revenue-stream-id', 'AjaxController@getStreamCategoryBasedOnRevenueStream');
                        
            // Route::post('get-stream-category-based-on-revenue-stream','AjaxController@getStreamCategoryBasedOnRevenueStream');
            Route::get('get-positions-based-on-departments', 'AjaxController@getPositionsBasedOnDepartments');
                        
            /**
             * * End expenses table
             */
                        
                        
        });
        /**
         * * Study Info
        */
                    
                        
                         
                         
    });
                    
                    
});
