import { get, patch, post, remove } from './base'
import { generateQueryString } from '../utils'

export function getConversationList (limit = '', offset = '') {
  const queryString = generateQueryString({ limit, offset })
  return get(`/conversations${queryString}`)
}

export function getConversation (conversationId) {
  return get(`/conversations/${conversationId}`)
}

export function getConversationIdForConversationWithUser (userId) {
  return get(`/user/${userId}/conversation`)
}

export function getMessages (conversationId, olderThanID) {
  return get(`/conversations/${conversationId}/messages?olderThanId=${olderThanID}`)
}

export function sendMessage (conversationId, body) {
  return post(`/conversations/${conversationId}/messages`, {
    body: body
  })
}

export function renameConversation (conversationId, newName) {
  return patch(`/conversations/${conversationId}`, {
    name: newName
  })
}

export function removeUserFromConversation (conversationId, userId) {
  return remove(`/conversations/${conversationId}/members/${userId}`)
}

export function createConversation (userIds) {
  return post('/conversations', {
    members: userIds
  })
}

export function markConversationRead (conversationId) {
  return post(`/conversations/${conversationId}/read`)
}
