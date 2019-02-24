import '@/core'
import '@/globals'
import 'jquery-dynatree'
import { vueRegister, vueApply } from '@/vue'
import StoreList from './components/StoreList.vue'
import { attachAddressPicker } from '@/addressPicker'
import {
  GET
} from '@/script'

if (GET('a') === 'undefined') {
  vueRegister({
    StoreList
  })
  vueApply('#vue-storelist')
}

if (GET('a') === 'edit' || GET('a') === 'new') {
  attachAddressPicker()
}
