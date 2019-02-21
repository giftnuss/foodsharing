import { get, post } from './base'

export async function getUpdates (pagenumber) {
  if (pagenumber == 0) {
    return (await get(`/../xhrapp.php?app=activity&m=load&listings=1`)).data.updates
  } else {
    return (await get(`/../xhrapp.php?app=activity&m=loadmore&page=${pagenumber}`)).data.updates
  }
}

export async function getOptionListings () {
  return (await get(`/../xhrapp.php?app=activity&m=load&listings=1`)).data.listings
}

export async function saveOptionListings (options) {
  var options_string = ""
  var new_option_id_inx = 0

  for (var option_id in options) {
    options[option_id].items = options[option_id].items.filter((a) => {return !a.checked})
    for (var item in options[option_id].items) {
      options_string += "&options[" + new_option_id_inx + "][index]=" + options[option_id].index + "&options[" + new_option_id_inx + "][id]=" + options[option_id].items[item].id
      new_option_id_inx += 1
    }
  }
  if (options_string == ""){
    options_string = "&select_all_options=true"
  }
  return (await get(`/../xhrapp.php?app=activity&m=load${options_string}`))
}

export async function sendQuickreply (href, msg) {
  return post('/..' + href, { msg })
}