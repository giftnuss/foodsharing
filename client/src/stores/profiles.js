import Vue from 'vue'

export default new Vue({
  data: {
    profiles: {}
  },
  methods: {
    updateFrom (profiles) {
      if (Array.isArray(profiles)) {
        for (const profile of profiles) {
          Vue.set(this.profiles, profile.id, convertProfile(profile))
        }
      }
    }
  }
})

export function convertProfile (val) {
  if (Array.isArray(val)) {
    return val.map(convertProfile)
  } else {
    return {
      ...val
    }
  }
}
