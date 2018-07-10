import Vue from 'vue'
import $ from 'jquery'
import i18n from '@/i18n'
import urls from '@/urls'
import { dateFormat } from '@/utils'
import 'bootstrap-vue/dist/bootstrap-vue.css'

Vue.filter('dateFormat', dateFormat)

Vue.filter('i18n', (key, variables = {}) => {
  console.warn(`i18n as a vue filter is deprecated. use i18n() as a vue functions`)
  return i18n(key, variables)
})
Vue.prototype.$i18n = (key, variables = {}) => {
  return i18n(key, variables)
}

Vue.prototype.$url = (key, ...params) => {
  return urls[key](...params)
}

export function vueRegister (components) {
  for (let key in components) {
    Vue.component(key, components[key])
  }
}

export function vueApply (selector) {
  let elements = $(selector)
  if (!elements.length) {
    throw new Error(`vueUse-Error: no elements were found with selector '${selector}'`)
  }
  elements.each((index, el) => {
    let componentName = $(el).data('vue-component')
    let props = $(el).data('vue-props') || {}
    let initialData = $(el).data('vue-initial-data') || {}

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
