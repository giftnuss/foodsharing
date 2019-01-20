import '@/core'
import '@/globals'
import 'jquery-dynatree'
import { vueRegister, vueApply } from '@/vue'
import StoreList from './components/StoreList.vue'

// import some legacy js files
// TODO: rewrite their usage with proper js modules using webpack

import '@/tablesorter'
import { attachAddresspicker } from '@/addresspicker'
import {
  GET
} from '@/script'

if (GET('a') === 'undefined') {
  vueRegister({
    StoreList
  })
  vueApply('#vue-storelist')
}

if (GET('a') === 'edit' || GET('a') === 'add') {
  attachAddresspicker()
}
