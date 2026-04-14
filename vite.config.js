import vue from '@vitejs/plugin-vue'
import laravel from 'laravel-vite-plugin'
import { defineConfig } from 'vite'

export default defineConfig({
  plugins: [
    laravel({
      input: [
		'resources/js/MoneyReceived/index.js',
		'resources/js/NonBanking/Views/Expenses/expenses.js',
		'resources/js/NonBanking/Views/FixedAssets/fixed-assets.ts',
		'resources/js/NonBanking/Views/LeasingFactoring/leasing.js',
		'resources/js/NonBanking/Views/DirectFactoring/direct-factoring.js',
		'resources/js/NonBanking/Views/ReverseFactoring/reverse-factoring.js',
		'resources/js/NonBanking/Views/IjaraMortgage/ijara-mortgage.js',
		'resources/js/NonBanking/Views/PortfolioMortgage/portfolio-mortgage.js',
		'resources/js/NonBanking/Views/ManpowerExpenses/manpower-expenses.js',
		'resources/js/NonBanking/Views/GeneralAssumptions/general-assumptions.ts',
		
		
		'resources/js/PropertyManagement/Views/Properties/properties.ts',
		'resources/js/PropertyManagement/Views/Properties/index.ts',
		'resources/js/PropertyManagement/Views/Contracts/index.ts',
		'resources/js/PropertyManagement/Views/Contracts/form.ts',
		'resources/js/PropertyManagement/Views/Expenses/expenses.js',
		'resources/js/PropertyManagement/Views/PropertyExpenses/property-expenses.js',
		'resources/js/PropertyManagement/Views/PropertyExpenses/property-expenses.js',
		'resources/js/PropertyManagement/Views/ForecastedProperties/forecasted-properties.js',
		'resources/js/PropertyManagement/Views/FixedAssets/fixed-assets.ts',
		'resources/js/PropertyManagement/Views/Dashboard/cashflow-forecast.ts',
		'resources/js/PropertyManagement/Views/ManpowerExpenses/manpower-expenses.js',
		'resources/js/PropertyManagement/Views/GeneralAssumptions/general-assumptions.ts',
		'resources/js/PropertyManagement/Views/occupied-properties/occupied-properties-with-full-rent-coverage-duration.ts',
		'resources/js/PropertyManagement/Views/occupied-properties/occupied-properties-with-partial-rent-coverage-duration.ts',
		'resources/js/PropertyManagement/Views/occupied-properties/properties-to-be-delivered.ts',
		
		'resources/js/NonBanking/Views/Spreadsheet/spread-sheet.js',
		
	
		
		
	],
      refresh: true,
    }),
    vue(),
	
  ],
  optimizeDeps: {
	include: [
		'@univerjs/core',
		'@univerjs/sheets',
		'@univerjs/sheets-ui',
		'@univerjs/ui',
		'@univerjs/engine-formula',
		'@univerjs/sheets-formula',
		
	],
	// مهم جداً بدونه Vite ممكن يتعطل
	exclude: [],
},
resolve: {
	alias: {
		'react': 'react',
		'react-dom': 'react-dom',
	}
}

  
})
