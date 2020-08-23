import { put } from './base'

export async function sendBuddyRequest (userId) {
  return (await put(`/buddy/${userId}`)).isBuddy
}
