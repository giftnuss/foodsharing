import { get } from './base'
import { generateQueryString } from '../utils'
import { ajax } from '@/script'

export function getConversationList (limit = '', offset = '') {
  const queryString = generateQueryString({ limit, offset })
  return get(`/conversations${queryString}`)
}

export function getConversation (conversationId) {
  return get(`/conversations/${conversationId}`)
}

// legacy MessageXhr method, going to be replaced by a proper REST Endpoint
export async function sendMessage (conversationId, message) {
  return new Promise((resolve, reject) => {
    ajax.req('msg', 'sendmsg', {
      loader: false,
      method: 'post',
      data: {
        c: conversationId,
        b: message
      },
      success: () => resolve(), // so that no one even tries to use the response data :D
      error: reject
    })
  })
}
