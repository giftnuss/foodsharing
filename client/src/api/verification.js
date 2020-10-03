import { patch, remove } from './base'

export async function verifyUser (userId) {
  return (await patch(`/user/${userId}/verification`))
}

export async function deverifyUser (userId) {
  return (await remove(`/user/${userId}/verification`))
}
