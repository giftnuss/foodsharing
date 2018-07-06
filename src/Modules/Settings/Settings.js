import '@/core'
import '@/globals'
import './Settings.css'
import 'jquery-jcrop'
import 'jquery-dynatree'
import 'typeahead'
import 'typeahead-addresspicker'
import 'leaflet'
import 'leaflet.awesome-markers'
import {
  fotoupload,
  picFinish
} from '@/script'
import { expose } from '@/utils'

expose({
  fotoupload,
  picFinish
})
