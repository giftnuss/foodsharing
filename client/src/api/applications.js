import { patch } from './base'

export async function acceptApplication (groupId, userId) {
  return await patch(`/applications/${groupId}/${userId}`)
}
