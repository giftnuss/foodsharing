import { post } from './base'

export function signup (storeId, pickupDate) {
  return post(`/stores/${storeId}/${pickupDate}/signup`)
}
