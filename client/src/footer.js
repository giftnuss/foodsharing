import { vueRegister, vueApply } from '@/vue'

import Footer from './components/Footer'

vueRegister({ Footer })
vueApply('#vue-footer', true)
