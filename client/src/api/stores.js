import { get, patch, post, remove } from './base'
import dateFnsParseISO from 'date-fns/parseISO'

export async function joinPickup (storeId, pickupDate, fsId) {
  const date = pickupDate.toISOString()
  return post(`/stores/${storeId}/pickups/${date}/${fsId}`)
}

export async function listPickups (storeId) {
  const res = await get(`/stores/${storeId}/pickups`)

  return res.pickups.map(c => ({
    ...c,
    date: dateFnsParseISO(c.date)
  }))
}

export async function leavePickup (storeId, pickupDate, fsId) {
  const date = pickupDate.toISOString()
  return remove(`/stores/${storeId}/pickups/${date}/${fsId}`)
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
