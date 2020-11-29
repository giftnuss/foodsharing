import '@/core'
import '@/globals'
import 'jquery-dynatree'
import { vueRegister, vueApply } from '@/vue'
import PollOverview from './components/PollOverview.vue'
import NewPollForm from './components/NewPollForm'
import EditPollForm from './components/EditPollForm'
import { GET } from '@/browser'

if (GET('sub') === 'new') {
  vueRegister({
    NewPollForm,
  })
  vueApply('#new-poll-form')
} else if (GET('sub') === 'edit') {
  vueRegister({
    EditPollForm,
  })
  vueApply('#edit-poll-form')
} else {
  vueRegister({
    PollOverview,
  })
  vueApply('#poll-overview')
}
