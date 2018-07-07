import Vue from 'vue'
import $ from 'jquery'
import i18n from '@/i18n'
import 'bootstrap-vue/dist/bootstrap-vue.css'

Vue.filter('i18n', (key, variables = {}) => {
  return i18n(key, variables)
})

export function vueUse (components) {
  $('.vue-wrapper').each((index, el) => {
    let elementName = $(el).data('element')
    let props = $(el).data('props') || {}

    if (!elementName) {
      throw new Error('fsvue-Error: missing element name. pass it as <div class="vue-wrapper" data-element="myelement" />')
    }

    // eslint-disable-next-line no-new
    new Vue({
      el,
      components,
      render (h) {
        return h(elementName, { props })
      }
    })
  })
}
