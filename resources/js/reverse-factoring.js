import { createApp } from 'vue'
import ExpenseComponent from './components/NonBanking/ReverseFactoringComponent.vue'

import PrimeVue from 'primevue/config'
import Aura from '@primeuix/themes/aura';



import { VueDatePicker } from '@vuepic/vue-datepicker'
import '@vuepic/vue-datepicker/dist/main.css'

import Swal from 'sweetalert2'
import Helper from './Helpers/Helper'

const app = createApp(ExpenseComponent)

app.component('VueDatePicker', VueDatePicker)

app.config.globalProperties.$swal = Swal
app.config.globalProperties.$helper = Helper

app.use(PrimeVue, {
    theme: {
        preset: Aura
    }
}).mount('#app-reverse-factoring')
