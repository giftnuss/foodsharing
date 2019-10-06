import dateFnsFormat from 'date-fns/format'
import dateFnsIsSameYear from 'date-fns/is_same_year'
import dateFnsIsDameDay from 'date-fns/is_same_day'
import dateFnsLocaleDE from 'date-fns/locale/de'
import dateFnsDistanceInWords from 'date-fns/distance_in_words'
import dateFnsAddDays from 'date-fns/add_days'

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
  switch (format) {
    case 'full-long':
      if (dateFnsIsDameDay(date, new Date())) {
        return dateFormat(date, '[heute], dddd, HH:mm [Uhr]')
      } else if (dateFnsIsDameDay(date, dateFnsAddDays(new Date(), 1))) {
        return dateFormat(date, '[morgen], dddd, HH:mm [Uhr]')
      } else if (dateFnsIsSameYear(date, new Date())) {
        return dateFormat(date, 'dddd, Do MMM, HH:mm [Uhr]')
      } else {
        return dateFormat(date, 'dd, Do MMM YYYY, HH:mm [Uhr]')
      }
    case 'full-short':
      if (dateFnsIsSameYear(date, new Date())) {
        return dateFormat(date, 'dd, DD. MMM, HH:mm')
      } else {
        return dateFormat(date, 'dd, DD. MMM YY, HH:mm')
      }
    default:
      return dateFnsFormat(date, format, { locale: dateFnsLocaleDE })
  }
}
export function dateDistanceInWords (date) {
  return dateFnsDistanceInWords(new Date(), date, {
    locale: dateFnsLocaleDE,
    addSuffix: true
  })
}

const noLocale = /^[\w-.\s,]*$/

/**
 * Compare function used in sorting of btable
 */
export function optimizedCompare (a, b, key) {
  const elemA = a[key]
  const elemB = b[key]
  if (typeof elemA === 'number' || (noLocale.test(elemA) && noLocale.test(elemB))) {
    if (typeof elemA === 'string') {
      const a = elemA.toLowerCase()
      const b = elemB.toLowerCase()
      return (a > b ? 1 : (a === b ? 0 : -1))
    }
    return (elemA > elemB ? 1 : (elemA === elemB ? 0 : -1))
  } else {
    return elemA.localeCompare(elemB)
  }
}

export const generateQueryString = params => {
  const qs = Object.keys(params)
    .filter(key => params[key] !== '')
    .map(key => key + '=' + params[key])
    .join('&')
  return qs.length ? `?${qs}` : ''
}
