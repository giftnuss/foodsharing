import { get, patch, post, remove } from './base'
import dateFnsParseISO from 'date-fns/parseISO'
import _ from 'underscore'

export async function listPickups (storeId) {
  const res = await get(`/stores/${storeId}/pickups`)

  return res.pickups.map(c => ({
    ...c,
    date: dateFnsParseISO(c.date),
  }))
}

export async function listPickupHistory (storeId, fromDate, toDate) {
  const from = fromDate.toISOString()
  const to = toDate.toISOString()
  const res = await get(`/stores/${storeId}/history/${from}/${to}`)
  const slots = res.pickups[0].occupiedSlots

  return _.groupBy(slots.map(s => ({
    ...s,
    storeId,
    isConfirmed: !!s.confirmed,
    date: dateFnsParseISO(s.date),
  })), 'date_ts')
}

export async function joinPickup (storeId, pickupDate, fsId) {
  const date = pickupDate.toISOString()
  return post(`/stores/${storeId}/pickups/${date}/${fsId}`)
}

export async function leavePickup (storeId, pickupDate, fsId, message) {
  const date = pickupDate.toISOString()
  return remove(`/stores/${storeId}/pickups/${date}/${fsId}`, {
    message: message,
  })
}

export async function confirmPickup (storeId, pickupDate, fsId) {
  const date = pickupDate.toISOString()
  return patch(`/stores/${storeId}/pickups/${date}/${fsId}`, { isConfirmed: true })
}

export async function setPickupSlots (storeId, pickupDate, totalSlots) {
  const date = pickupDate.toISOString()
  return patch(`/stores/${storeId}/pickups/${date}`, { totalSlots: totalSlots })
}

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

export async function acceptStoreRequest (storeId, userId) {
  return patch(`/stores/${storeId}/requests/${userId}`)
}

export async function removeStoreRequest (storeId, userId) {
  return remove(`/stores/${storeId}/requests/${userId}`)
}
