import Vue from 'vue'
const mediaQuery = {
  xs: {
    max: 575
  },
  sm: {
    min: 576,
    max: 749
  },
  md: {
    min: 750,
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
    wXL: false
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
    }
  }
})

ui.$nextTick(function () {
  window.addEventListener('resize', this.updateWindowWidth)
  this.updateWindowWidth()
})

export default ui
