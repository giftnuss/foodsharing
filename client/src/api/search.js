import { get } from './base'

// wrapper around the legacy SearchXHR method
// API response should be in format:

// interface InstantSearchResult {
//   stores: InstantSearchResultElement[]
//   regions: InstantSearchResultElement[]
//   users: InstantSearchResultElement[]
//   groups: InstantSearchResultElement[]
// }

// interface InstantSearchResultElement {
//   id: number
//   name: string
//   teaser?: string
//   image?: string
// }
import i18n from '@/i18n'
// imports the translations file

function mapOldResponseToNewFormat (data) {
  const mapping = {
    [i18n('dashboard.my.users')]: 'users',
    [i18n('storelist.region')]: 'regions',
    [i18n('betrieb')]: 'stores',
    [i18n('menu.entry.your_groups')]: 'myGroups',
    [i18n('menu.entry.your_stores')]: 'myStores',
    [i18n('dashboard.my.regions')]: 'myRegions',
    [i18n('dashboard.my.buddies')]: 'myBuddies',
  }
  return data.reduce((o, el) => {
    const key = mapping[el.title]
    o[key] = el.result.map(i => ({
      id: i.id || parseInt(i.href.match(/id=(.*?)(&|$)/)[1]),
      name: i.name,
      image: i.img || null,
      teaser: i.teaser || null,
    }))
    return o
  }, { groups: [] })
}

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
  return mapOldResponseToNewFormat(await get('/search/legacyindex'))
}

export async function searchForum (groupId, subforumId, query) {
  return await get(`/search/forum/${groupId}/${subforumId}?q=${encodeURIComponent(query)}`)
}
