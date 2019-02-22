import { get, post } from './base'

export async function getUpdates (pagenumber) {
  return (await get(`/../xhrapp.php?app=activity&m=load&page=${pagenumber}`)).data.updates
}

export async function getOptionListings () {
  return (await get(`/../xhrapp.php?app=activity&m=getoptionlist`)).data.listings
}

export async function saveOptionListings (options) {
  var optionsString = ''
  var newOptionIdInx = 0

  for (var optionId in options) {
    options[optionId].items = options[optionId].items.filter((a) => { return !a.checked })
    for (var item in options[optionId].items) {
      optionsString += '&options[' + newOptionIdInx + '][index]=' + options[optionId].index + '&options[' + newOptionIdInx + '][id]=' + options[optionId].items[item].id
      newOptionIdInx += 1
    }
  }
  if (optionsString === '') {
    optionsString = '&select_all_options=true'
  }
  return get(`/../xhrapp.php?app=activity&m=setoptionlist${optionsString}`)
}

export async function sendQuickreply (href, msg) {
  return post('/..' + href, { msg })
}
