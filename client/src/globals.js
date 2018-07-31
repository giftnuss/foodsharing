/* eslint-disable camelcase */

/*
  Make some things from the webpack environment available globally on the window object.

  This is to allow webpack-enabled pages to still have a few bits of inline js:
  - inline click handlers
  - addJs scripts
  - addJsFunc scripts

*/

import $ from 'jquery'

import conv from '@/conv'
import info from '@/info'
import socket from '@/socket'

import { expose } from '@/utils'

import {
  betrieb,
  chat,
  pulseInfo,
  pulseError,
  pulseSuccess,
  profile,
  goTo,
  reload,
  dialogs,
  ajreq,
  ajax,
  u_loadCoords,
  showLoader,
  hideLoader,
  becomeBezirk,
  preZero,
  betriebRequest,
  rejectBetriebRequest,
  error
} from '@/script'

import {
  u_printChildBezirke
} from '@/becomeBezirk'

expose({
  $,
  jQuery: $,
  betrieb,
  chat,
  pulseInfo,
  pulseError,
  pulseSuccess,
  profile,
  goTo,
  reload,
  dialogs,
  ajreq,
  ajax,
  u_loadCoords,
  showLoader,
  hideLoader,
  becomeBezirk,
  preZero,
  betriebRequest,
  rejectBetriebRequest,
  u_printChildBezirke,
  conv,
  info,
  error,
  sock: socket
})
