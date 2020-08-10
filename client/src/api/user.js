import { post, put, remove } from './base'

export function login (email, password, rememberMe) {
  return post('/user/login', { email, password, remember_me: rememberMe })
}

export function deleteUser (id) {
  return remove(`/user/${id}`)
}

export function registerUser (firstName, lastName, email, password, gender, birthdate, mobilePhone, subscribeNewsletter) {
  return post('/user', {
    firstname: firstName,
    lastname: lastName,
    email: email,
    password: password,
    gender: gender,
    birthdate: birthdate,
    mobilePhone: mobilePhone,
    subscribeNewsletter: subscribeNewsletter
  })
}

export function testRegisterEmail (email) {
  return post('/user/isvalidemail', { email: email })
}

export function sendBanana (id, message) {
  return put(`/user/${id}/banana`, { message: message })
}
