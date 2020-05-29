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
  academy: () => '/?page=content&sub=academy',
  basket: (basketId) => `/essenskoerbe/${basketId}`,
  baskets: () => '/essenskoerbe',
  blog: () => '/news',
  blogEdit: () => '/?page=blog&sub=manage',
  claims: () => '/?page=content&sub=forderungen',
  communitiesAustria: () => '/?page=content&sub=communitiesAustria',
  communitiesGermany: () => '/?page=content&sub=communitiesGermany',
  communitiesSwitzerland: () => '/?page=content&sub=communitiesSwitzerland',
  contact: () => '/?page=content&sub=contact',
  contentEdit: () => '/?page=content',
  conversations: (conversationId = null) => `/?page=msg${conversationId ? `&cid=${conversationId}` : ''}`,
  dashboard: () => '/?page=dashboard',
  dataprivacy: () => '/?page=legal',
  donate: () => '/unterstuetzung',
  email: () => '/?page=email',
  event: (eventId) => `/?page=event&id=${eventId}`,
  events: (regionId) => `/?page=bezirk&bid=${regionId}&sub=events`,
  fairteiler: (regionId) => `/?page=bezirk&bid=${regionId}&sub=fairteiler`,
  faq: () => '/faq',
  faqEdit: () => '/?page=faq',
  festival: () => '/?page=content&sub=festival',
  foodsaverList: (regionId) => `/?page=foodsaver&bid=${regionId}`,
  foodsharepoint: (regionId, fspId) => `?page=fairteiler&sub=ft&bid=${regionId}&id=${fspId}`,
  foodsharepoints: (regionId) => `/?page=bezirk&bid=${regionId}&sub=fairteiler`,
  fsstaedte: () => '/?page=content&sub=fsstaedte',
  grundsaetze: () => 'https://wiki.foodsharing.de/Grundsätze',
  guide: () => '/ratgeber',
  home: () => '/',
  imprint: () => '/impressum',
  infos: () => '/?page=content&sub=infohub',
  infosCompany: () => '/fuer-unternehmen',
  international: () => '/?page=content&sub=international',
  joininfo: () => '/?page=content&sub=joininfo',
  leeretonne: () => '/?page=content&sub=leeretonne',
  legal_agreement: () => 'https://wiki.foodsharing.de/Rechtsvereinbarung',
  login: () => '/?page=login',
  logout: () => '/?page=logout',
  mailbox: () => '/?page=mailbox',
  mailboxManage: () => '/?page=mailbox&a=manage',
  map: () => '/karte',
  members: (regionId) => `/?page=bezirk&bid=${regionId}&sub=members`,
  mission: () => '/ueber-uns',
  partner: () => '/partner',
  passports: (regionId) => `/?page=passgen&bid=${regionId}`,
  press: () => '/?page=content&sub=presse',
  quizEdit: () => '/?page=quiz',
  region: () => '/?page=region',
  releaseNotes: () => '/?page=content&sub=releaseNotes',
  reports: (regionId = null) => regionId ? `/?page=report&bid=${regionId}` : '/?page=report',
  settings: () => '/?page=settings',
  statistic: (regionId) => `/?page=bezirk&bid=${regionId}&sub=statistic`,
  statistics: () => '/statistik',
  store: (storeId) => `/?page=betrieb&id=${storeId}`,
  storeAdd: (regionId = null) => regionId ? `/?page=betrieb&a=new&bid=${regionId}` : '/?page=betrieb&a=new',
  storeList: () => '/?page=fsbetrieb',
  stores: (regionId) => `/?page=betrieb&bid=${regionId}`,
  team: () => '/team',
  transparency: () => '/?page=content&sub=transparency',
  upgradeToFs: () => '/?page=settings&sub=upgrade/up_fs',
  wall: (regionId) => `/?page=bezirk&bid=${regionId}&sub=wall`,
  wiki: () => 'https://wiki.foodsharing.de/',
  workingGroupEdit: (groupId) => `/?page=groups&sub=edit&id=${groupId}`,
  workingGroups: (regionId = null) => regionId ? `/?page=groups&p=${regionId}` : '/?page=groups',
  workshops: () => '/?page=content&sub=workshops'
}

const url = (key, ...params) => {
  if (!urls[key]) {
    console.error(new Error(`url() Error: url key '${key}' does not exist`))
    return '#'
  }
  return urls[key](...params)
}

export { url, urls }
