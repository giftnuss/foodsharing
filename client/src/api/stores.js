import { get, post } from './base'

export async function signup (storeId, pickupDate) {
  return post(`/stores/${storeId}/${pickupDate}/signup`)
}

export async function listPickups (storeId) {
  const res = await get(`/stores/${storeId}/pickups`)

  return res.data.map(c => ({
    ...c,
    date: new Date(c.date)
  }))
}
