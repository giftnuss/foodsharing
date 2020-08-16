import { post, put, remove } from './base'

export function login (email, password, rememberMe) {
  return post('/user/login', { email, password, remember_me: rememberMe })
}

export function deleteUser (id) {
  return remove(`/user/${id}`)
}

export function testRegisterEmail (email) {
  return post('/user/isvalidemail', { email: email })
}

export function sendBanana (id, message) {
  return put(`/user/${id}/banana`, { message: message })
}
