/* eslint-disable camelcase */

/*
  Make some things from the webpack environment available globally.

  This is to allow webpack-enabled pages to still have a few bits of inline js:
  - inline click handlers
  - addJs scripts
  - addJsFunc scripts

  Anything exported will be available globally (on the window object).
*/

/*

Actually, for now I will try to only expose things globally per-page/module level.
See Dashboard.js for the first example of that.

import $ from 'jquery'
import {
  pulseInfo, pulseError, pulseSuccess, dialogs, ajreq, ajax, u_loadCoords, showLoader, hideLoader
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
  conv,
  info
})
*/

export default {}
