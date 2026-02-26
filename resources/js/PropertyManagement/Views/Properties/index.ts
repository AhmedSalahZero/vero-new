import { createApp } from 'vue';
import { createPinia } from 'pinia';
import PropertiesIndexComponent from './PropertiesIndexComponent.vue';

import Aura from '@primeuix/themes/aura';
import PrimeVue from 'primevue/config';
import Tooltip from 'primevue/tooltip';
import { createI18n } from 'vue-i18n'

import Swal from 'sweetalert2';
import Helper from '../../../Helpers/Helper';

const app = createApp(PropertiesIndexComponent)
const pinia = createPinia()

app.config.globalProperties.$swal = Swal
app.config.globalProperties.$helper = Helper

const i18n = createI18n({
    legacy: false,
    locale: 'ar',
    messages: {
        ar: window.translations,
    },
})

app.directive('tooltip', Tooltip);

app.use(pinia)
.use(PrimeVue, {
    theme: {
        preset: Aura
    }
})
.use(i18n)
.mount('#app-properties-index')
