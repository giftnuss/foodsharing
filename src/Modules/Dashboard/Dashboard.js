import '@/core'
import '@/globals'

import activity from '@/activity'

import { vueRegister, vueApply } from '@/vue'
import DashboardThread from './components/ActivityThread'

activity.init()

vueRegister({
    DashboardThread
})

vueApply('#activity-thread')


