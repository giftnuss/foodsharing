import { get } from './base'

// wrapper around the legacy SearchXHR method
export async function getConversationList () {
  return (await get('/../xhrapp.php?app=msg&m=loadconvlist')).data.convs.map(c => ({
    id: parseInt(c.id),
    title: c.name,
    lastMessageTime: c.last,
    members: c.member.map((m) => ({
      id: parseInt(m.id),
      name: m.name,
      avatar: m.photo
    })),
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
