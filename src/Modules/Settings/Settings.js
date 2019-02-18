/* eslint-disable camelcase */
import '@/core'
import '@/globals'
import './Settings.css'
import 'jquery-jcrop'
import 'jquery-dynatree'
import { attachAddressPicker } from '@/addressPicker'

import {
  fotoupload,
  picFinish,
  collapse_wrapper
} from '@/script'
import { expose } from '@/utils'

expose({
  fotoupload,
  picFinish,
  collapse_wrapper
})

attachAddressPicker()
