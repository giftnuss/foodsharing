import { get, patch, post } from './base'

export async function getUpdates (pagenumber) {
  return (await get(`/../xhrapp.php?app=activity&m=load&page=${pagenumber}`)).data.updates
}

export async function getOptionListings () {
  return (await get('/activities/options'))
}

export async function saveOptionListings (options) {
  const request = []
  for (var optionId in options) {
    options[optionId].items = options[optionId].items.filter((a) => { return !a.checked })
    for (var item in options[optionId].items) {
      request.push({
        index: options[optionId].index,
        id: options[optionId].items[item].id
      })
    }
  }
  return patch('/activities/options', {
    options: request
  })
}

export async function sendQuickreply (href, msg) {
  return post('/..' + href, { msg })
}
