import { post } from './base'

export function login (email, password) {
  return post(`/user/login`, { email, password })
}
