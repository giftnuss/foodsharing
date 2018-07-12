// these are used for generating link-paths inside vue
// e.g. $url('profile', 15)

export default {
  profile: (id) => `/profile/${id}`,
  store: (id) => `/?page=betrieb&id=${id}`,
  forum: (regionId, regionSubId = 0) => `/?page=bezirk&bid=${regionId}&sub=${regionSubId === 1 ? 'botforum' : 'forum'}`,
  fairteiler: (regionId) => `/?page=bezirk&bid=${regionId}&sub=fairteiler`,
  events: (regionId) => `/?page=bezirk&bid=${regionId}&sub=events`,
  stores: (regionId) => `/?page=betrieb&bid=${regionId}`,
  workingGroups: (regionId) => `/?page=groups&p=${regionId}`,
  wall: (regionId) => `/?page=bezirk&bid=${regionId}&sub=wall`,
  foodsaverList: (regionId) => `/?page=foodsaver&bid=${regionId}`,
  passports: (regionId) => `/?page=passgen&bid=${regionId}`,
  search: (query) => `/?page=search&q=${encodeURIComponent(query)}`

}
