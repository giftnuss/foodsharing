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

import {
  pulseInfo,
  pulseError,
  pulseSuccess,
  profile,
  goTo,
  dialogs,
  ajreq,
  ajax,
  u_loadCoords,
  showLoader,
  hideLoader,
  becomeBezirk
} from '@/script'

import {
  u_printChildBezirke
} from '@/becomeBezirk'

Object.assign(window, {
  $,
  jQuery: $,
  pulseInfo,
  pulseError,
  pulseSuccess,
  profile,
  goTo,
  dialogs,
  ajreq,
  ajax,
  u_loadCoords,
  showLoader,
  hideLoader,
  becomeBezirk,
  u_printChildBezirke,
  conv,
  info,
  sock: socket
})
