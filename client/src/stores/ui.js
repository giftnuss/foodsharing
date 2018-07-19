import Vue from 'vue'
import serverData from '@/server-data'
import { GET } from '@/script'

const mediaQuery = {
  xs: {
    max: 575
  },
  sm: {
    min: 576,
    max: 753
  },
  md: {
    min: 754,
    max: 991
  },
  lg: {
    min: 992,
    max: 1999
  },
  xl: {
    min: 1200
  }
}

const ui = new Vue({
  data: {
    windowWidth: 0,
    wXS: false,
    wSM: false,
    wMD: false,
    wLG: false,
    wXL: false,

    activeRegionId: null
  },
  methods: {
    updateWindowWidth (event) {
      let w = document.documentElement.clientWidth
      this.windowWidth = w
      this.wXS = w <= mediaQuery.xs.max
      this.wSM = w >= mediaQuery.sm.min && w <= mediaQuery.sm.max
      this.wMD = w >= mediaQuery.md.min && w <= mediaQuery.md.max
      this.wLG = w >= mediaQuery.lg.min && w <= mediaQuery.lg.max
      this.wXL = w >= mediaQuery.xl.min

      console.log(w)
    },
    updateRegionId () {
      let regionId
      if (['bezirk', 'betrieb', 'foodsaver', 'passgen'].indexOf(serverData.page) !== -1 && GET('bid')) regionId = parseInt(GET('bid'))
      else if (serverData.page === 'groups' && GET('p')) regionId = parseInt(GET('p'))
      else if (localStorage.getItem('lastRegion')) regionId = parseInt(localStorage.getItem('lastRegion'))
      else if (serverData.user.regularRegion) regionId = serverData.user.regularRegion

      console.log('last regionId', regionId)
      if (regionId !== this.activeRegionId) {
        this.activeRegionId = regionId
        localStorage.setItem('lastRegion', regionId)
      }
    }
  }
})

ui.$nextTick(function () {
  window.addEventListener('resize', this.updateWindowWidth)
  this.updateWindowWidth()
  this.updateRegionId()
})

export default ui
