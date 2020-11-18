// these are used for generating link-paths inside vue
// e.g. $url('profile', 15)

const urls = {
  profile: (id) => `/profile/${id}`,
  forum: (regionId, subforumId = 0, threadId = null, postId = null, newThread = false) => {
    return (`/?page=bezirk&bid=${regionId}` +
      `&sub=${(subforumId === 1) ? 'botforum' : 'forum'}` +
      (threadId === null ? '' : `&tid=${threadId}`) +
      (postId === null ? '' : `&pid=${postId}#post-${postId}`) +
      (newThread ? '&newthread=1' : '')
    )
  },
  academy: () => '/?page=content&sub=academy',
  basket: (basketId) => `/essenskoerbe/${basketId}`,
  baskets: () => '/essenskoerbe',
  blog: () => '/news',
  blogAdd: () => '/?page=blog&sub=add',
  blogEdit: (blogId) => `/?page=blog&sub=edit&id=${blogId}`,
  blogList: () => '/?page=blog&sub=manage',
  changelog: () => '/?page=content&sub=changelog',
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
  support: () => 'https://foodsharing.freshdesk.com/support/home',
  festival: () => '/?page=content&sub=festival',
  foodsaverList: (regionId) => `/?page=foodsaver&bid=${regionId}`,
  foodsharepoint: (regionId, fspId) => `?page=fairteiler&sub=ft&bid=${regionId}&id=${fspId}`,
  foodsharepoints: (regionId) => `/?page=bezirk&bid=${regionId}&sub=fairteiler`,
  fsstaedte: () => '/?page=content&sub=fsstaedte',
  grundsaetze: () => 'https://wiki.foodsharing.de/Grundsätze',
  guide: () => 'https://wiki.foodsharing.de/Hygiene-Ratgeber_f%C3%BCr_Lebensmittel',
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
  mailbox: (mailboxId = null) => `/?page=mailbox${mailboxId ? `&show=${mailboxId}` : ''}`,
  mailboxManage: () => '/?page=mailbox&a=manage',
  map: () => '/karte',
  members: (regionId) => `/?page=bezirk&bid=${regionId}&sub=members`,
  mission: () => '/ueber-uns',
  partner: () => '/partner',
  passports: (regionId) => `/?page=passgen&bid=${regionId}`,
  passwordReset: () => '/?page=login&sub=passwordReset',
  poll: (pollId) => `/?page=poll&id=${pollId}`,
  pollEdit: (pollId) => `/?page=poll&id=${pollId}&sub=edit`,
  pollNew: (regionId) => `/?page=poll&bid=${regionId}&sub=new`,
  polls: (regionId) => `/?page=bezirk&bid=${regionId}&sub=polls`,
  press: () => '/?page=content&sub=presse',
  quizEdit: () => '/?page=quiz',
  region: () => '/?page=region',
  releaseNotes: () => '/?page=content&sub=releaseNotes',
  reports: (regionId = null) => regionId ? `/?page=report&bid=${regionId}` : '/?page=report',
  settings: () => '/?page=settings',
  statistic: (regionId) => `/?page=bezirk&bid=${regionId}&sub=statistic`,
  statistics: () => '/statistik',
  store: (storeId) => `/?page=fsbetrieb&id=${storeId}`,
  storeAdd: (regionId = null) => regionId ? `/?page=betrieb&a=new&bid=${regionId}` : '/?page=betrieb&a=new',
  storeList: () => '/?page=fsbetrieb',
  stores: (regionId) => `/?page=betrieb&bid=${regionId}`,
  team: () => '/team',
  transparency: () => '/?page=content&sub=transparency',
  upgradeToFs: () => '/?page=settings&sub=upgrade/up_fs',
  wall: (regionId) => `/?page=bezirk&bid=${regionId}&sub=wall`,
  wiki: () => 'https://wiki.foodsharing.de/',
  wiki_voting: () => 'https://wiki.foodsharing.de/Abstimmungs-Modul',
  workingGroupEdit: (groupId) => `/?page=groups&sub=edit&id=${groupId}`,
  workingGroups: (regionId = null) => regionId ? `/?page=groups&p=${regionId}` : '/?page=groups',
  workshops: () => '/?page=content&sub=workshops',
  urlencode: (url) => encodeURIComponent(`${url}`),
  donations: () => 'https://spenden.foodsharing.de',
  circle_of_friends: () => 'https://spenden.foodsharing.de/freundeskreis',
  selfservice: () => 'https://spenden.foodsharing.de/selfservice',
  devdocs: () => 'https://devdocs.foodsharing.network',
  hoster: () => 'https://manitu.de',
  wiener_tafel: () => 'https://www.wienertafel.at',
  bmlfuw: () => 'https://www.bmlrt.gv.at',
  denns: () => 'https://www.denns-biomarkt.at',
  resendActivationMail: () => '/?page=login&a=resendActivationMail',
}

const url = (key, ...params) => {
  if (!urls[key]) {
    console.error(new Error(`url() Error: url key '${key}' does not exist`))
    return '#'
  }
  return urls[key](...params)
}

export { url, urls }
