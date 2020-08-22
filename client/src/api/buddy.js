import { post } from './base'

export async function sendBuddyRequest (userId) {
  return (await post(`/buddy/${userId}`)).isBuddy
}
