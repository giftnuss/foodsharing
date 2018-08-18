import { get } from './base'

export async function getBaskets () {
  return (await get('/../xhrapp.php?app=basket&m=infobar')).data.baskets
}
