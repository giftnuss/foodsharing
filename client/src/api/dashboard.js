import { get } from './base'

export async function getUpdates (pagenumber) {
  if (pagenumber == 0) {
    return (await get(`/../xhrapp.php?app=activity&m=load&listings=1`)).data.updates
  } else {
    return (await get(`/../xhrapp.php?app=activity&m=loadmore&page=${pagenumber}`)).data.updates
  }
}