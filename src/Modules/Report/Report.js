import '@/core'
import '@/globals'
import '@/tablesorter'
import { vueRegister, vueApply } from '@/vue'

import ReportList from './components/ReportList.vue'
import { GET } from '@/script'

if (GET('a') === 'undefined') {
  vueRegister({
    ReportList
  })
  vueApply('#vue-reportlist')
}
