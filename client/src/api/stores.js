import { get, patch, post, remove } from './base'
import dateFnsParseISO from 'date-fns/parseISO'

function normalizeStoreWallPost (post) {
  post.createdAt = dateFnsParseISO(post.createdAt)
  post.body = post.text
  delete post.text
  return post
}

export async function getStoreWall (storeId) {
  const posts = (await get(`/stores/${storeId}/posts`))
  return posts.map(normalizeStoreWallPost)
}

export async function writeStorePost (storeId, text) {
  const newPost = (await post(`/stores/${storeId}/posts`, { text })).post
  return normalizeStoreWallPost(newPost)
}

export async function deleteStorePost (storeId, postId) {
  return remove(`/stores/${storeId}/posts/${postId}`)
}

export async function listStoresForCurrentUser () {
  return get('/user/current/stores')
}

export async function requestStoreTeamMembership (storeId, userId) {
  return post(`/stores/${storeId}/requests/${userId}`)
}

export async function acceptStoreRequest (storeId, userId, moveToStandby) {
  return patch(`/stores/${storeId}/requests/${userId}`, { moveToStandby })
}

export async function declineStoreRequest (storeId, userId) {
  return remove(`/stores/${storeId}/requests/${userId}`)
}

export async function promoteToStoreManager (storeId, userId) {
  return patch(`/stores/${storeId}/managers/${userId}`)
}

export async function demoteAsStoreManager (storeId, userId) {
  return remove(`/stores/${storeId}/managers/${userId}`)
}

export async function addStoreMember (storeId, userId) {
  return post(`/stores/${storeId}/members/${userId}`)
}

export async function removeStoreMember (storeId, userId) {
  return remove(`/stores/${storeId}/members/${userId}`)
}

export async function moveMemberToStandbyTeam (storeId, userId) {
  return patch(`/stores/${storeId}/members/${userId}/standby`)
}

export async function moveMemberToRegularTeam (storeId, userId) {
  return remove(`/stores/${storeId}/members/${userId}/standby`)
}
