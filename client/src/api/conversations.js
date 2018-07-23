import { get } from './base'

// wrapper around the legacy SearchXHR method
export async function getConversationList () {
  const res = await get('/../xhrapp.php?app=msg&m=loadconvlist&raw=1')
  if (!res.data.convs) return []
  return res.data.convs.map(c => ({
    id: parseInt(c.id),
    title: c.name,
    lastMessageTime: new Date(c.last.replace(/ /, 'T')),
    members: c.member && c.member instanceof Array ? c.member.map((m) => ({
      id: parseInt(m.id),
      name: m.name,
      avatar: m.photo ? '/images/mini_q_' + m.photo : null
    })) : [],
    lastMessage: {
      bodyRaw: c.last_message,
      authorId: c.last_foodsaver_id
    },
    hasUnreadMessages: c.unread === '1'
  }))
}

export function getConversation (conversationId) {
  return get(`/conversations/${conversationId}`)
}
