import { get, post } from './base'
import dateFnsParseISO from 'date-fns/parseISO'

export async function getBaskets () {
  const baskets = (await get('/../xhrapp.php?app=basket&m=infobar')).data.baskets
  return baskets.map(basket => {
    basket.requests = basket.requests.map(request => {
      request.time = dateFnsParseISO(request.time)
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
