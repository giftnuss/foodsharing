import '@/core'
import '@/globals'

// import activity from '@/activity'

import { vueRegister, vueApply } from '@/vue'
import ActivityOverview from './components/ActivityOverview'

// activity.init()

vueRegister({
    ActivityOverview
})

vueApply('#activity-overview')


