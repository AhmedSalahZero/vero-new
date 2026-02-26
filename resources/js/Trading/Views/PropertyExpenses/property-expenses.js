import { createApp } from 'vue'
import { createI18n } from 'vue-i18n'
import PropertyExpensesComponent from './PropertyExpensesComponent.vue'

import Aura from '@primeuix/themes/aura'
import PrimeVue from 'primevue/config'



import { VueDatePicker } from '@vuepic/vue-datepicker'
import '@vuepic/vue-datepicker/dist/main.css'

import Swal from 'sweetalert2'
import Helper from '../../../Helpers/Helper'

const app = createApp(PropertyExpensesComponent)

const i18n =createI18n({
    legacy: false,
    locale: 'ar',
    messages: {
        ar: window.translations,
    },
})

app.component('VueDatePicker', VueDatePicker)

app.config.globalProperties.$swal = Swal
app.config.globalProperties.$helper = Helper

app.use(PrimeVue, {
    theme: {
        preset: Aura
    }
})
.use(i18n)
.mount('#app-property-expenses')
