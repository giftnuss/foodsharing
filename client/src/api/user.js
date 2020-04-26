import { get, post, remove } from './base'

export function login (email, password) {
  return post('/user/login', { email, password })
}

export function deleteUser (id) {
  return remove(`/user/${id}`)
}

export async function testRegisterEmail(email) {
  return get('user/validemail', { email: email })
}
