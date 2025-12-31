import vue from '@vitejs/plugin-vue'
import laravel from 'laravel-vite-plugin'
import { defineConfig } from 'vite'

export default defineConfig({
  plugins: [
    laravel({
      input: [
		
		'resources/js/expenses.js',
		
	],
      refresh: true,
    }),
    vue(),
	
  ],
  
})
