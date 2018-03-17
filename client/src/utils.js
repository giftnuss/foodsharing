import $ from 'jquery'

import { ajreq } from '@/script'

// This is data that is defined by the server in lib/inc.php
export const ServerData = window.ServerData

export function getBrowserLocation (success) {
  if (ServerData.location) return success(ServerData.location)
  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(pos => {
      ajreq('savebpos', {
        app: 'map',
        lat: pos.coords.latitude,
        lon: pos.coords.longitude
      })
      success({
        lat: pos.coords.latitude,
        lon: pos.coords.longitude
      })
    })
  }
}

export function start (fn) {
  $(fn)
}
