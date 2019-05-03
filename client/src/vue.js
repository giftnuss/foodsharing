import Vue from 'vue'
import i18n from '@/i18n'
import urls from '@/urls'
import { dateFormat, dateDistanceInWords } from '@/utils'
import 'bootstrap-vue/dist/bootstrap-vue.css'

Vue.filter('dateFormat', dateFormat)
Vue.filter('dateDistanceInWords', dateDistanceInWords)

Vue.filter('i18n', (key, variables = {}) => {
  console.warn(`i18n as a vue filter is deprecated. use i18n() as a vue functions`)
  return i18n(key, variables)
})
Vue.prototype.$i18n = (key, variables = {}) => {
  return i18n(key, variables)
}

Vue.prototype.$url = (key, ...params) => {
  if (!urls[key]) {
    console.error(new Error(`Vue.$url() Error: url key '${key}' does not exist`))
    return '#'
  }
  return urls[key](...params)
}

export function vueRegister (components) {
  for (let key in components) {
    Vue.component(key, components[key])
  }
}

export function vueApply (selector) {
  let elements = document.querySelectorAll(selector)

  // querySelectorAll().forEach() is broken in iOS 9
  elements = Array.from(elements)

  if (!elements.length) {
    throw new Error(`vueUse-Error: no elements were found with selector '${selector}'`)
  }
  elements.forEach((el, index) => {
    let componentName = el.getAttribute('data-vue-component')
    let props = JSON.parse(el.getAttribute('data-vue-props')) || {}
    let initialData = JSON.parse(el.getAttribute('vue-initial-data')) || {}

    if (!componentName) {
      throw new Error('vueUse-Error: missing component name. pass it as <div data-vue-component="my-component" />')
    }

    // eslint-disable-next-line no-new
    let vm = new Vue({
      el,
      render (h) {
        return h(componentName, { props })
      }
    })
    if (initialData && typeof initialData === 'object') {
      for (let key in initialData) {
        if (typeof vm.$children[0][key] === 'undefined' || typeof vm.$children[0][key] === 'function') {
          throw new Error(`vueUse() Error: prop '${key}' needs to be defined in data()`)
        }
        vm.$children[0][key] = initialData[key]
      }
    }
  })
}
