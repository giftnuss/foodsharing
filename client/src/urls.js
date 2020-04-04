// these are used for generating link-paths inside vue
// e.g. $url('profile', 15)

const urls = {
  profile: (id) => `/profile/${id}`,
  forum: (regionId, isAmb = false, topicId = null, postId = null) => {
    return (`/?page=bezirk&bid=${regionId}` +
      `&sub=${isAmb ? 'botforum' : 'forum'}` +
      (topicId === null ? '' : `&tid=${topicId}`) +
      (postId === null ? '' : `&pid=${postId}#tpost-${postId}`)
    )
  },
  fairteiler: (regionId) => `/?page=bezirk&bid=${regionId}&sub=fairteiler`,
  foodsharepoint: (regionId, fspId) => `?page=fairteiler&sub=ft&bid=${regionId}&id=${fspId}`,
  foodsharepoints: (regionId) => `/?page=bezirk&bid=${regionId}&sub=fairteiler`,
  members: (regionId) => `/?page=bezirk&bid=${regionId}&sub=members`,
  statistic: (regionId) => `/?page=bezirk&bid=${regionId}&sub=statistic`,
  event: (eventId) => `/?page=event&id=${eventId}`,
  events: (regionId) => `/?page=bezirk&bid=${regionId}&sub=events`,
  store: (storeId) => `/?page=betrieb&id=${storeId}`,
  stores: (regionId) => `/?page=betrieb&bid=${regionId}`,
  storeList: () => '/?page=fsbetrieb',
  storeAdd: (regionId = null) => regionId ? `/?page=betrieb&a=new&bid=${regionId}` : '/?page=betrieb&a=new',
  workingGroups: (regionId = null) => regionId ? `/?page=groups&p=${regionId}` : '/?page=groups',
  workingGroupEdit: (groupId) => `/?page=groups&sub=edit&id=${groupId}`,
  wall: (regionId) => `/?page=bezirk&bid=${regionId}&sub=wall`,
  foodsaverList: (regionId) => `/?page=foodsaver&bid=${regionId}`,
  passports: (regionId) => `/?page=passgen&bid=${regionId}`,
  conversations: (conversationId = null) => `/?page=msg${conversationId ? `&cid=${conversationId}` : ''}`,
  dashboard: () => '/?page=dashboard',
  map: () => '/karte',
  home: () => '/',
  mailbox: () => '/?page=mailbox',
  settings: () => '/?page=settings',
  logout: () => '/?page=logout',
  joininfo: () => '/?page=content&sub=joininfo',
  basket: (basketId) => `/essenskoerbe/${basketId}`,
  baskets: () => '/essenskoerbe',
  upgradeToFs: () => '/?page=settings&sub=upgrade/up_fs',
  mission: () => '/ueber-uns',
  claims: () => '/?page=content&sub=forderungen',
  fsstaedte: () => '/?page=content&sub=fsstaedte',
  leeretonne: () => '/?page=content&sub=leeretonne',
  academy: () => '/?page=content&sub=academy',
  workshops: () => '/?page=content&sub=workshops',
  festival: () => '/?page=content&sub=festival',
  international: () => '/?page=content&sub=international',
  transparency: () => '/?page=content&sub=transparency',
  contact: () => '/?page=content&sub=contact',
  dataprivacy: () => '/?page=legal',
  legal_agreement: () => 'https://wiki.foodsharing.de/Rechtsvereinbarung',
  partner: () => '/partner',
  statistics: () => '/statistik',
  infosCompany: () => '/fuer-unternehmen',
  infos: () => '/?page=content&sub=infohub',
  blog: () => '/news',
  faq: () => '/faq',
  guide: () => '/ratgeber',
  wiki: () => 'https://wiki.foodsharing.de/',
  grundsaetze: () => 'https://wiki.foodsharing.de/Grundsätze',
  communitiesGermany: () => '/?page=content&sub=communitiesGermany',
  communitiesAustria: () => '/?page=content&sub=communitiesAustria',
  communitiesSwitzerland: () => '/?page=content&sub=communitiesSwitzerland',
  team: () => '/team',
  press: () => '/?page=content&sub=presse',
  imprint: () => '/impressum',
  donate: () => '/unterstuetzung',
  changelog: () => '/?page=content&sub=changelog',
  reports: (regionId) => `/?page=report&bid=${regionId}`,
  login: () => '/?page=login'
}

const url = (key, ...params) => {
  if (!urls[key]) {
    console.error(new Error(`url() Error: url key '${key}' does not exist`))
    return '#'
  }
  return urls[key](...params)
}

export { url, urls }
