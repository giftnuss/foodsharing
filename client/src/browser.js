import $ from 'jquery'

export function goTo (url) {
  if (url !== '#') {
    document.location.href = url
  }
}

export function isMob () {
  return $(window).width() < 900
}

const HTTP_GET_VARS = []
const strGET = document.location.search.substr(1, document.location.search.length)

if (strGET !== '') {
  let gArr = strGET.split('&')
  for (let i = 0; i < gArr.length; ++i) {
    let v = ''; let vArr = gArr[i].split('=')
    if (vArr.length > 1) { v = vArr[1] }
    HTTP_GET_VARS[unescape(vArr[0])] = unescape(v)
  }
}

export function GET (v) {
  if (!HTTP_GET_VARS[v]) { return 'undefined' }
  return HTTP_GET_VARS[v]
}
