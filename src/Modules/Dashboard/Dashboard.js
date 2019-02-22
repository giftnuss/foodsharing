import '@/core'
import '@/globals'

import { vueRegister, vueApply } from '@/vue'
import ActivityOverview from './components/ActivityOverview'

vueRegister({
  ActivityOverview
})

vueApply('#activity-overview')
