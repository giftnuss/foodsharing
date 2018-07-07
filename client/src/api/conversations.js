import { get } from './base'

export function getConversation (conversationId) {
  return get(`/conversations/${conversationId}`)
}
