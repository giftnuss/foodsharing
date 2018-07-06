import Vue from 'vue'
import $ from 'jquery'

import 'bootstrap-vue/dist/bootstrap-vue.css'

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
