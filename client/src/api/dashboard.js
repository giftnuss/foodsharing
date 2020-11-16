import { get, patch, post } from './base'

export async function getUpdates (pagenumber) {
  return (await get(`/activities/updates?page=${pagenumber}`)).updates
}

export async function getFilters () {
  return (await get('/activities/filters'))
}

export async function setFilters (options) {
  const excluded = []
  for (var optionId in options) {
    options[optionId].items = options[optionId].items.filter((a) => { return !a.included })
    for (var item in options[optionId].items) {
      excluded.push({
        index: options[optionId].index,
        id: options[optionId].items[item].id,
      })
    }
  }
  return patch('/activities/filters', {
    excluded: excluded,
  })
}

export async function sendQuickreply (href, msg) {
  return post('/..' + href, { msg })
}
