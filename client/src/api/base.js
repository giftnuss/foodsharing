const BASE_URL = '/api'
const DEFAULT_OPTIONS = {
  method: 'GET',
  credentials: 'same-origin',
  mode: 'cors'
}
if (window.fetch) window.fetch.activeFetchCalls = 0

export class HTTPError extends Error {
  constructor (code, text) {
    super(`HTTP Error ${code}: ${text}`)
    this.code = code
    this.statusText = text
  }
}

export async function request (path, options = {}) {
  try {
    window.fetch.activeFetchCalls++
    const res = await window.fetch(BASE_URL + path, Object.assign({}, DEFAULT_OPTIONS, options))
    if (!res.ok) {
      throw new HTTPError(res.status, res.statusText)
    }
    if (res.status === 204) {
      window.fetch.activeFetchCalls--
      return {}
    } else {
      const json = await res.json()
      window.fetch.activeFetchCalls--
      return json
    }
  } catch (err) {
    console.error(err)
    window.fetch.activeFetchCalls--
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
      'Content-Type': 'application/json; charset=utf-8'
    },
    body: JSON.stringify(body)
  })
}

export function put (path, body) {
  return request(path, {
    method: 'PUT',
    headers: {
      'Content-Type': 'application/json; charset=utf-8'
    },
    body: JSON.stringify(body)
  })
}

export function patch (path, body) {
  return request(path, {
    method: 'PATCH',
    headers: {
      'Content-Type': 'application/json; charset=utf-8'
    },
    body: JSON.stringify(body)
  })
}

// delete is a reserved word, therefore we use remove
export function remove (path, body = {}) {
  return request(path, {
    method: 'DELETE',
    headers: {
      'Content-Type': 'application/json; charset=utf-8'
    },
    body: JSON.stringify(body)
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
