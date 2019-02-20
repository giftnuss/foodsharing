import { get, post, remove } from './base'

export function getWallPosts (target, targetId) {
  return get(`/api/wall/${target}/${targetId}`)
}

export function addPost (target, targetId, body) {
  return post(`/api/wall/${target}/${targetId}`, body)
}

export function deletePost (target, targetId, postId) {
  return remove(`/api/wall/${target}/${targetId}/${postId}`)
}
