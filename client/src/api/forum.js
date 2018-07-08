import { get, post, put, patch, remove } from './base'

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
  return patch(`/forum/thread/${threadId}`, {
    isSticky: true
  })
}

export function unstickThread (threadId) {
  return patch(`/forum/thread/${threadId}`, {
    isSticky: false
  })
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

export function addReaction (postId, key) {
  return post(`/forum/post/${postId}/reaction/${key}`)
}

export function removeReaction (postId, key) {
  return remove(`/forum/post/${postId}/reaction/${key}`)
}
