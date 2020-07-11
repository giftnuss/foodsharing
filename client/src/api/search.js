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

function mapOldResponseToNewFormat (data) {
  const mapping = {
    Foodsaver: 'users',
    Bezirk: 'regions',
    'Kooperationsbetrieb/e': 'stores',
    'Deine Gruppen': 'myGroups',
    'Deine Betriebe': 'myStores',
    'Deine Bezirke': 'myRegions',
    'Menschen die Du kennst': 'myBuddies'
  }
  return data.reduce((o, el) => {
    const key = mapping[el.title]
    o[key] = el.result.map(i => ({
      id: i.id || parseInt(i.href.match(/id=(.*?)(&|$)/)[1]),
      name: i.name,
      image: i.img || null,
      teaser: i.teaser || null
    }))
    return o
  }, { groups: [] })
}

export async function instantSearch (query) {
  return await get(`/search/all?q=${encodeURIComponent(query)}`)
}

export async function instantSearchIndex () {
  return mapOldResponseToNewFormat(await get('/search/legacyindex'))
}

export async function searchForum (groupId, query, ambassadorForum) {
  return await get(`/search/forum/${groupId}?q=${encodeURIComponent(query)}&ambassadorForum=${ambassadorForum ? 1 : 0}`)
}
