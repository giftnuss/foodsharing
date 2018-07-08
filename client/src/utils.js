import dateFnsFormat from 'date-fns/format'
import dateFnsIsSameYear from 'date-fns/is_same_year'
import dateFnsLocaleDE from 'date-fns/locale/de'

import { ajreq } from '@/script'

import serverData from '@/server-data'

export function getBrowserLocation (success) {
  if (serverData.location) return success(serverData.location)
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

/**
 * Make things available globally via the browser window object
 *
 * This is to allow functions to be called from non-webpack javascript.
 * e.g. click handlers, or xhr javascript responses
 *
 * @param data object of things to expose globally
 */
export function expose (data) {
  Object.assign(window, data)
}

export function dateFormat (date, format = 'full-long') {
  if (format === 'full-long') {
    if (dateFnsIsSameYear(date, new Date())) {
      return dateFormat(date, 'dddd, Do MMM, HH:mm [Uhr]')
    } else {
      return dateFormat(date, 'dddd, Do MMM YYYY, HH:mm [Uhr]')
    }
  } else if (format === 'full-short') {
    if (dateFnsIsSameYear(date, new Date())) {
      return dateFormat(date, 'dd, DD. MMM, HH:mm')
    } else {
      return dateFormat(date, 'dd, DD. MMM YY, HH:mm')
    }
  } else {
    return dateFnsFormat(date, format, { locale: dateFnsLocaleDE })
  }
}
