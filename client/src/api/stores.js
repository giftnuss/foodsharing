import { get, patch, post, remove } from './base'
import parse from 'date-fns/parse'

export async function joinPickup (storeId, pickupDate, fsId) {
  const date = pickupDate.toISOString()
  return post(`/stores/${storeId}/pickups/${date}/${fsId}`)
}

export async function listPickups (storeId) {
  const res = await get(`/stores/${storeId}/pickups`)

  return res.pickups.map(c => ({
    ...c,
    date: parse(c.date)
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
