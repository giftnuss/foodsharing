import { get, put, remove } from './base'
import dateFnsParseISO from 'date-fns/parseISO'
import _ from 'underscore'

export async function listPastPickups (fsId, fromDate, toDate) {
  const from = fromDate.toISOString()
  const to = toDate.toISOString()
  const res = await get(`/foodsaver/${fsId}/pickups/${from}/${to}`)
  const slots = res.pickups[0].occupiedSlots

  return _.groupBy(slots.map(s => ({
    ...s,
    isConfirmed: true,
    date: dateFnsParseISO(s.date),
  })), (s) => { return s.storeId + '-' + s.date_ts })
}

export function sendBanana (id, message) {
  return put(`/user/${id}/banana`, { message: message })
}

export function deleteBanana (userId, senderId) {
  return remove(`/user/${userId}/banana/${senderId}`)
}

export function removeUserFromBounceList (userId) {
  return remove(`/user/${userId}/emailbounce`)
}
