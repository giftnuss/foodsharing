import { patch } from './base'

export async function setEmailStatus (emailId, read) {
  return patch(`/emails/${emailId}/${read ? 1 : 0}`)
}
