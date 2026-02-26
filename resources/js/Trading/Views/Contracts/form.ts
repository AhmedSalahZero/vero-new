import { createApp } from 'vue'
import ContractsComponent from './ContractsComponent.vue'
import Aura from '@primeuix/themes/aura'
import PrimeVue from 'primevue/config'
import { createI18n } from 'vue-i18n'

const app = createApp(ContractsComponent)

const i18n = createI18n({
	legacy: false,
	locale: 'ar',
	messages: {
		ar: window.translations,
	},
})

app.use(PrimeVue, {
	theme: {
		preset: Aura,
	},
})

app.use(i18n)

const appElement = document.querySelector('#app-contracts-form')
if (appElement) {
	app.mount(appElement)
}
