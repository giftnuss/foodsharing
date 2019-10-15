import { get, post } from './base'

export async function getBaskets () {
  return (await get('/../xhrapp.php?app=basket&m=infobar')).data.baskets
}

export async function requestBasket (basketId, message) {
  return (post(`/baskets/${basketId}/request`, {
    message: message
  }))
}

export async function withdrawBasketRequest (basketId) {
  return (post(`/baskets/${basketId}/withdraw`))
}
