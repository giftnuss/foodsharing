const BASE_URL = '/api'
const DEFAULT_OPTIONS = {
  method: 'GET',
  credentials: 'same-origin',
  mode: 'cors',
  headers: {},
}
if (self.fetch) self.fetch.activeFetchCalls = 0

export function getCsrfToken () {
  if (!document.cookie) return null
  const match = document.cookie.match(/CSRF_TOKEN=([0-9a-f]+)/)
  if (!match) return null
  return match[1]
}

export class HTTPError extends Error {
  constructor (code, text, method, url) {
    super(`HTTP Error ${code}: ${text} during ${method} ${url}`)
    this.code = code
    this.statusText = text
  }
}

export async function request (path, options = {}) {
  try {
    self.fetch.activeFetchCalls++
    const o = Object.assign({}, DEFAULT_OPTIONS, options)
    const csrfToken = getCsrfToken()
    if (csrfToken) o.headers['X-CSRF-Token'] = csrfToken
    const request = new self.Request(BASE_URL + path, o)
    const res = await self.fetch(request)
    if (!res.ok) {
      throw new HTTPError(res.status, res.statusText, request.method, request.url)
    }
    if (res.status === 204) {
      self.fetch.activeFetchCalls--
      return {}
    } else {
      const json = await res.json()
      self.fetch.activeFetchCalls--
      return json
    }
  } catch (err) {
    console.error(err)
    self.fetch.activeFetchCalls--
    throw err
  }
}

export function get (path) {
  return request(path)
}

export function post (path, body) {
  return request(path, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json; charset=utf-8',
    },
    body: JSON.stringify(body),
  })
}

export function put (path, body) {
  return request(path, {
    method: 'PUT',
    headers: {
      'Content-Type': 'application/json; charset=utf-8',
    },
    body: JSON.stringify(body),
  })
}

export function patch (path, body) {
  return request(path, {
    method: 'PATCH',
    headers: {
      'Content-Type': 'application/json; charset=utf-8',
    },
    body: JSON.stringify(body),
  })
}

// delete is a reserved word, therefore we use remove
export function remove (path, body = {}) {
  return request(path, {
    method: 'DELETE',
    headers: {
      'Content-Type': 'application/json; charset=utf-8',
    },
    body: JSON.stringify(body),
  })
}

// const sleep = (ms) => new Promise((resolve) => setTimeout(resolve, ms))
// export async function dummyRequest (response = []) {
//   await sleep(1000)
//   if (Math.random() > 0.5) {
//     return response
//   } else {
//     throw new HTTPError(500, 'Dummy request failed')
//   }
// }
