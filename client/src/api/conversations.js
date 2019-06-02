import { get, post } from './base'
import { generateQueryString } from '../utils'

export function getConversationList (limit = '', offset = '') {
  const queryString = generateQueryString({ limit, offset })
  return get(`/conversations${queryString}`)
}

export function getConversation (conversationId) {
  return get(`/conversations/${conversationId}`)
}

export function getMessages (conversationId, olderThanID) {
  return get(`/conversations/${conversationId}/messages?olderThanId=${olderThanID}`)
}

export function sendMessage (conversationId, body) {
  return post(`/conversations/${conversationId}`, {
    body: body
  })
}
