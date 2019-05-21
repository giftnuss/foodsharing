import { get, patch, post, remove } from './base'
import parse from 'date-fns/parse'

export async function joinPickup (storeId, pickupDate, fsId) {
  const date = pickupDate.toISOString()
  return post(`/stores/${storeId}/${date}/${fsId}`)
}

export async function listPickups (storeId) {
  const res = await get(`/stores/${storeId}/pickups`)

  return res.data.map(c => ({
    ...c,
    date: parse(c.date)
  }))
}

export async function leavePickup (storeId, pickupDate, fsId) {
  const date = pickupDate.toISOString()
  return remove(`/stores/${storeId}/${date}/${fsId}`)
}

export async function confirmPickup (storeId, pickupDate, fsId) {
  const date = pickupDate.toISOString()
  return patch(`/stores/${storeId}/${date}/${fsId}`, { isConfirmed: true })
}

export async function removePickupSlot (storeId, pickupDate) {
  const date = pickupDate.toISOString()
  return patch(`/stores/${storeId}/${date}`, { removeSlot: true })
}

export async function addPickupSlot (storeId, pickupDate) {
  const date = pickupDate.toISOString()
  return patch(`/stores/${storeId}/${date}`, { addSlot: true })
}
