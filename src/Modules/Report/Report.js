import '@/core'
import '@/globals'
import '@/tablesorter'
import { vueRegister, vueApply } from '@/vue'

import ReportList from './components/ReportList.vue'
import { GET } from '@/script'

if (GET('a') === 'undefined') {
  // The container for the report list only exists if a region specific page is requested
  var reportListContainerId = 'vue-reportlist'
  if (document.getElementById(reportListContainerId)) {
    vueRegister({
      ReportList
    })
    vueApply('#' + reportListContainerId)
  }
}
