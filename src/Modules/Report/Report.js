import '@/core'
import '@/globals'
import '@/tablesorter'
import { vueRegister, vueApply } from '@/vue'

import ReportList from './components/ReportList.vue'
import { GET } from '@/script'
import { serverData } from '@/server-data'

if (GET('a') === 'undefined') {
  vueRegister({
    ReportList
  })
  vueApply('#vue-reportlist')
}
