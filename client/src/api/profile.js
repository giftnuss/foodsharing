import { put, remove } from './base'

export function sendBanana (id, message) {
  return put(`/user/${id}/banana`, { message: message })
}

export function deleteBanana (userId, senderId) {
  return remove(`/user/${userId}/banana/${senderId}`)
}

export function removeUserFromBounceList (userId) {
  return remove(`/user/${userId}/emailbounce`)
}
