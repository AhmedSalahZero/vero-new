import { createApp } from 'vue'
import SpreadSheetComponent from './SpreadSheetComponent.vue'

import Aura from '@primeuix/themes/aura'
import PrimeVue from 'primevue/config'



import { VueDatePicker } from '@vuepic/vue-datepicker'
import '@vuepic/vue-datepicker/dist/main.css'

import Swal from 'sweetalert2'
import Helper from '../../../Helpers/Helper'

const app = createApp(SpreadSheetComponent)

app.component('VueDatePicker', VueDatePicker)

app.config.globalProperties.$swal = Swal
app.config.globalProperties.$helper = Helper

app.use(PrimeVue, {
    theme: {
        preset: Aura
    }
}).mount('#app-spread-sheet')
