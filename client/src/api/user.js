import { post, remove } from './base'

export function login (email, password) {
  return post('/user/login', { email, password })
}

export function deleteUser (id) {
  return remove(`/user/${id}`)
}

export function testRegisterEmail (email) {
  return post('/user/isvalidemail', { email: email })
}
