import { get } from './base'
import { generateQueryString } from '../utils'

export function getConversationList (limit = '', offset = '') {
  const queryString = generateQueryString({ limit, offset })
  return get(`/conversations${queryString}`)
}

export function getConversation (conversationId) {
  return get(`/conversations/${conversationId}`)
}
