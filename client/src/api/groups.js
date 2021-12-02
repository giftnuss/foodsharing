import { remove, post } from './base'

export function deleteGroup (id) {
  return remove(`/groups/${id}`)
}

export function removeMember (groupId, memberId) {
  return remove(`/groups/${groupId}/members/${memberId}`)
}

export function addMember (groupId, memberId) {
  return post(`/groups/${groupId}/members/${memberId}`)
}
