import { get } from './base'

export async function instantSearch (query) {
  return await get(`/search/all?q=${encodeURIComponent(query)}`)
}

export async function searchUser (query, regionId = null) {
  let path = `/search/user?q=${encodeURIComponent(query)}`
  if (regionId !== null) {
    path += `&regionId=${regionId}`
  }
  return await get(path)
}

export async function instantSearchIndex () {
  return await get('/search/index')
}

export async function searchForum (groupId, subforumId, query) {
  return await get(`/search/forum/${groupId}/${subforumId}?q=${encodeURIComponent(query)}`)
}
