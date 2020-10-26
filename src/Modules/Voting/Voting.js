import '@/core'
import '@/globals'
import 'jquery-dynatree'
import { vueRegister, vueApply } from '@/vue'
import PollOverview from './components/PollOverview.vue'
import NewPollForm from './components/NewPollForm'
import { GET } from '@/browser'

if (GET('sub') === 'new') {
  vueRegister({
    NewPollForm,
  })
  vueApply('#new-poll-form')
} else {
  vueRegister({
    PollOverview,
  })
  vueApply('#poll-overview')
}
