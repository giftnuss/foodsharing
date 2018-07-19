import { get, HTTPError } from './base'

export async function login (username, password) {
  // currently the password is sent as a GET parameter, that is absolutely inacceptable
  // because in that way the password is stored in the logs (client & server side)
  // this should be changed and the old server logs should get deleted afterwards
  let res = await get(`/../xhrapp.php?app=api&m=login&e=${username}&p=${password}`)
  if (res.status === 1) {
    return {
      name: res.name
    }
  } else {
    throw new HTTPError(401)
  }
}
