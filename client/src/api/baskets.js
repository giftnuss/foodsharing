import { get, post } from './base'

export async function getBaskets () {
  const baskets = (await get('/baskets?type=mine')).baskets
  return baskets.map(basket => {
    basket.createdAt = new Date(basket.createdAt * 1000)
    basket.updatedAt = new Date(basket.updatedAt * 1000)
    basket.requests = basket.requests.map(request => {
      request.time = new Date(request.time * 1000)
      return request
    })
    return basket
  })
}

export async function requestBasket (basketId, message) {
  return (post(`/baskets/${basketId}/request`, {
    message: message
  }))
}

export async function withdrawBasketRequest (basketId) {
  return (post(`/baskets/${basketId}/withdraw`))
}
