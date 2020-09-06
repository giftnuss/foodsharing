import { get, patch, remove } from './base'

// wrapper around the legacy SearchXHR method
export async function getBellList () {
  return await get('/bells')
}

export function deleteBell (id) {
  return remove(`/bells/${id}`)
}

/**
 * Returns the number of bells that were successfully marked as read.
 */
export async function markBellsAsRead (ids) {
  return (await patch('/bells', {
    ids: ids
  })).marked
}
