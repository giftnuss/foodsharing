import 'css/jquery.fullPage.css'

import $ from 'jquery'
import 'fullpage.js'
import serverData from '@/server-data'

const mainEl = $('main')
const footerEl = $('footer')

mainEl.hide()
footerEl.hide()

const sliders = serverData.sliders || []

for (const slider of sliders) {
  const { id, sections, anchors, colors, tooltips } = slider

  const el = $(`#${id}`)

  el.fullpage({
    anchors: anchors,
    sectionsColor: colors,
    navigation: true,
    navigationPosition: 'right',
    navigationTooltips: tooltips,
    responsive: 900,
    onLeave: function (index) {
      // ' . $onleafejs . '
      // TODO: check if it needs to run the onleave/afterload stuff... is available in the sections variable
    },
    afterLoad: function (anchorLink, index) {
      // ' . $afterloadjs . '

      if (index === sections.length) {
        el.find('footer').show()
      } else {
        el.find('footer').hide()
      }
    }
  })

  el.find('.section').css('visibility', 'visible')

  if (footerEl.length > 0) {
    el.find('.section:last .fp-tableCell:last').append(
      `<footer style="display:none;bottom:0px;width:100%;position:absolute;" class="footer">${footerEl.html()}</footer>`
    )
    footerEl.remove()
  }
}
