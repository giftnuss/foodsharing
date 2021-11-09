import { remove } from './base'

export function deleteGroup (id) {
  return remove(`/groups/${id}`)
}

export function removeMember (groupId, memberId) {
  return remove(`/groups/${groupId}/members/${memberId}`)
}
