import dateFnsFormat from 'date-fns/format'
import dateFnsIsSameDay from 'date-fns/isSameDay'
import dateFnsIsSameYear from 'date-fns/isSameYear'
import dateFnsLocaleDE from 'date-fns/locale/de'
import dateFnsFormatDistance from 'date-fns/formatDistance'
import dateFnsAddDays from 'date-fns/addDays'
// awesome-phonenumber is used by vue-tel-input and no explicit dep:
import PhoneNumber from 'awesome-phonenumber'

import { ajreq } from '@/script'

import serverData from '@/server-data'

export function getBrowserLocation (success) {
  if (serverData.location) return success(serverData.location)
  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(pos => {
      ajreq('savebpos', {
        app: 'map',
        lat: pos.coords.latitude,
        lon: pos.coords.longitude,
      })
      success({
        lat: pos.coords.latitude,
        lon: pos.coords.longitude,
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
  try {
    switch (format) {
      case 'day':
        return dateFormat(date, 'd.M.yyyy')
      case 'full-long':
        if (dateFnsIsSameDay(date, new Date())) {
          return dateFormat(date, "'heute', cccc, HH:mm 'Uhr'")
        } else if (dateFnsIsSameDay(date, dateFnsAddDays(new Date(), 1))) {
          return dateFormat(date, "'morgen', cccc, HH:mm 'Uhr'")
        } else if (dateFnsIsSameYear(date, new Date())) {
          return dateFormat(date, "cccc, do MMM, HH:mm 'Uhr'")
        } else {
          return dateFormat(date, "cccccc, do MMM yyyy, HH:mm 'Uhr'")
        }
      case 'full-short':
        if (dateFnsIsSameYear(date, new Date())) {
          return dateFormat(date, 'cccccc, d. MMM, HH:mm')
        } else {
          return dateFormat(date, 'cccccc, d.M.yyyy, HH:mm')
        }
      default:
        return dateFnsFormat(date, format, { locale: dateFnsLocaleDE })
    }
  } catch (error) {
    console.error({ error, date })
  }
}
export function dateDistanceInWords (date) {
  return dateFnsFormatDistance(date, new Date(), {
    locale: dateFnsLocaleDE,
    addSuffix: true,
  })
}

const noLocale = /^[\w-.\s,]*$/
const noPhoneDigit = /[^+0-9]/g

export function callableNumber (number, allowInvalid = false) {
  if (!number) {
    return ''
  }
  let digits = number.toString()
  // check for invalid +49(0) numbers that we can try to "rescue" later:
  // (this will fail for `+49 (0)` etc which are not worth the effort)
  digits = digits.replace(/^(\+\d{1,3})\(0\)/, '$1')
  // now strip the remaining non-number characters aside from country code:
  digits = digits.replace(noPhoneDigit, '')
  // convert an implicit country code into the expected format:
  // maybe it's given as 0049 instead of +49?
  digits = digits.replace(/^00/, '+')

  const phone = new PhoneNumber(digits)
  if (phone.isValid()) {
    return 'tel:' + digits
  } else if (allowInvalid) {
    return (digits.length > 6) ? digits : ''
  } else {
    return ''
  }
}

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

function autoLink (text) {
  const pattern = /(^|\s)((?:https?|ftp):\/\/([-A-Z0-9+\u0026@#/%?=()~_|!:,.;]*[-A-Z0-9+\u0026@#/%=~()_|]))/gi
  const currentHost = document.location.host

  return text.replace(pattern, function (match, space, url, urlWithoutProto) {
    return `${space}<a href="${url}" ${urlWithoutProto.split('/', 2)[0] !== currentHost ? ' target="_blank"' : ''}>${urlWithoutProto}</a>`
  })
}

function nl2br (str) {
  const breakTag = '<br>'
  return (`${str}`).replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, `$1${breakTag}$2`)
}

export function plainToHtml (string) {
  const entityMap = {
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
  }
  return autoLink(nl2br(String(string).replace(/[&<>]/g, function fromEntityMap (s) {
    return entityMap[s]
  })))
}

export function plainToHtmlAttribute (string) {
  const entityMap = {
    '"': '&quot',
    "'": '&#39;',
  }
  return String(string).replace(/["']/g, function fromEntityMap (s) {
    return entityMap[s]
  },
  )
}

export function isWebGLSupported () {
  // https://stackoverflow.com/a/22953053
  try {
    var canvas = document.createElement('canvas')
    return !!window.WebGLRenderingContext &&
      (canvas.getContext('webgl') || canvas.getContext('experimental-webgl'))
  } catch (e) {
    return false
  }
}
