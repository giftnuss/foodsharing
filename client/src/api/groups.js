import { remove } from './base'

export function deleteGroup (id) {
  return remove(`/groups/${id}`)
}
