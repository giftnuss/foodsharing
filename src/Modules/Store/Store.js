import '@/core'
import '@/globals'
import 'jquery-dynatree'
import { vueUse } from '@/vue'
import StoreList from './components/StoreList.vue'

// import some legacy js files
// TODO: rewrite their usage with proper js modules using webpack
import 'typeahead'
import 'typeahead-addresspicker'
import 'leaflet'
import 'leaflet.awesome-markers'
import '@/tablesorter'

vueUse({
  StoreList
})
