import { get, post, put, remove } from './base'

export function getThread (threadId) {
  return get(`/forum/thread/${threadId}`)
}

export function followThread (threadId) {
  return post(`/forum/thread/${threadId}/follow`)
}

export function unfollowThread (threadId) {
  return remove(`/forum/thread/${threadId}/follow`)
}

export function stickThread (threadId) {
  return post(`/forum/thread/${threadId}/stick`)
}

export function unstickThread (threadId) {
  return remove(`/forum/thread/${threadId}/stick`)
}

export function createPost (threadId, body) {
  return post(`/forum/post`, {
    threadId: threadId,
    body: body
  })
}

export function updatePost (postId, body) {
  return put(`/forum/post`, {
    body: body
  })
}

export function deletePost (postId) {
  return remove(`/forum/post/${postId}`)
}

export function addReaction (postId, emoji) {
  return post(`/forum/post/${postId}/reaction`, {
    emoji: emoji
  })
}

export function removeReaction (postId, emoji) {
  return remove(`/forum/post/${postId}/reaction`, {
    emoji: emoji
  })
}
