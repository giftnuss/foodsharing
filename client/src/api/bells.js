import { get } from './base'

// wrapper around the legacy SearchXHR method
export async function getBellList () {
  return (await get('/../xhrapp.php?app=bell&m=infobar')).data.list
}

export function deleteBell (id) {
  return get(`/../xhrapp.php?app=bell&m=delbell&id=${id}`)
}

export function markBellAsRead(id) {
    return get(`/../xhrapp.php?app=bell&m=markBellAsRead&id=${id}`)
}