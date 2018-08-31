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
    'Foodsaver': 'users',
    'Bezirk': 'regions',
    'Kooperationsbetrieb/e': 'stores',
    'Deine Gruppen': 'myGroups',
    'Deine Betriebe': 'myStores',
    'Deine Bezirke': 'myRegions',
    'Menschen die Du kennst': 'myBuddies'
  }
  return data.reduce((o, el) => {
    let key = mapping[el.title]
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
  return mapOldResponseToNewFormat((await get(`/../xhrapp.php?app=search&m=search&s=${encodeURIComponent(query)}`)).data)
}

export async function instantSearchIndex (token) {
  return mapOldResponseToNewFormat(await get(`/../cache/searchindex/${encodeURIComponent(token)}.json`))
}
