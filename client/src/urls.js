// these are used for generating link-paths inside vue
// e.g. $url('profile', 15)

export default {
  profile: (id) => `/profile/${id}`,
  store: (id) => `?page=betrieb&id=${id}`
}
