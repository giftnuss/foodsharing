import { get, patch, post, remove } from './base'
import dateFnsParseISO from 'date-fns/parseISO'
import _ from 'underscore'

export async function listPickups (storeId) {
  const res = await get(`/stores/${storeId}/pickups`)

  return res.pickups.map(c => ({
    ...c,
    date: dateFnsParseISO(c.date),
  }))
}

export async function listPickupHistory (storeId, fromDate, toDate) {
  const from = fromDate.toISOString()
  const to = toDate.toISOString()
  const res = await get(`/stores/${storeId}/history/${from}/${to}`)
  const slots = res.pickups[0].occupiedSlots

  return _.groupBy(slots.map(s => ({
    ...s,
    isConfirmed: !!s.confirmed,
    date: dateFnsParseISO(s.date),
  })), 'date_ts')
}

export async function joinPickup (storeId, pickupDate, fsId) {
  const date = pickupDate.toISOString()
  return post(`/stores/${storeId}/pickups/${date}/${fsId}`)
}

export async function leavePickup (storeId, pickupDate, fsId, message) {
  const date = pickupDate.toISOString()
  return remove(`/stores/${storeId}/pickups/${date}/${fsId}`, {
    message: message,
  })
}

export async function confirmPickup (storeId, pickupDate, fsId) {
  const date = pickupDate.toISOString()
  return patch(`/stores/${storeId}/pickups/${date}/${fsId}`, { isConfirmed: true })
}

export async function setPickupSlots (storeId, pickupDate, totalSlots) {
  const date = pickupDate.toISOString()
  return patch(`/stores/${storeId}/pickups/${date}`, { totalSlots: totalSlots })
}

export async function deleteStorePost (postId) {
  return remove(`/stores/posts/${postId}`)
}

export async function listStoresForCurrentUser () {
  return get('/user/current/stores')
}

export async function removeStoreRequest (storeId, userId) {
  return remove(`/stores/${storeId}/requests/${userId}`)
}
