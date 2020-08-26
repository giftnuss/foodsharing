import '@/core'
import '@/globals'
import 'jquery-dynatree'
import { vueRegister, vueApply } from '@/vue'
import PollOverview from './components/PollOverview.vue'

vueRegister({
  PollOverview
})
vueApply('#poll-overview')
