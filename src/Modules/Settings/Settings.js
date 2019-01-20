/* eslint-disable camelcase */
import '@/core'
import '@/globals'
import './Settings.css'
import 'jquery-jcrop'
import 'jquery-dynatree'
import { attachAddresspicker } from '@/addresspicker'

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

attachAddresspicker()
