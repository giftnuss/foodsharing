/* eslint-disable camelcase */

import '@/core'

import activity from '@/activity'

import $ from 'jquery'
import {
  pulseInfo,
  pulseError,
  pulseSuccess,
  dialogs,
  ajreq,
  ajax,
  u_loadCoords,
  showLoader,
  hideLoader,
  becomeBezirk
} from '@/script'

import conv from '@/conv'
import info from '@/info'

Object.assign(window, {
  $,
  jQuery: $,
  pulseInfo,
  pulseError,
  pulseSuccess,
  dialogs,
  ajreq,
  ajax,
  u_loadCoords,
  showLoader,
  hideLoader,
  becomeBezirk,
  conv,
  info
})

activity.init()
