
import $ from 'jquery'
import 'tablesorter'
import 'tablesorter-pagercontrols'
import 'css/tablesorter.css'

// duplicated in /js/tablesorter.js
$('table.tablesorter').each((index, table) => {
  const pager = $(table).next()

  const options = {
    headers: {},
    wigets: ['zebrea']
  }

  // column disabled for sorting? set in the options
  $('> thead th', table).each((index, el) => {
    if ($(el).data('sort') === false) options.headers[index] = {sorter: false}
  })
  const sorter = $(table).tablesorter(options)

  if (pager.length) {
    sorter.tablesorterPager({container: $(pager)})

    $('.prev', pager).button({
      icons: {
        primary: 'ui-icon-circle-arrow-w'
      },
      text: false
    })
    $('.next', pager).button({
      icons: {
        primary: 'ui-icon-circle-arrow-e'
      },
      text: false
    })
  }
})
