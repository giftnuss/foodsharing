/* eslint-disable eqeqeq */
import $ from 'jquery'
import _ from 'underscore'
import { ajax } from '@/script'
import serverData from '@/server-data'

const search = {
  initiated: false,
  isSearching: false,
  lastSearch: '',
  index: false,
  $icon: null,
  $searchbar: null,
  $result: null,
  $indexResult: null,
  $input: null,
  $morelink: null,

  init: function () {
    this.$icon = $('#searchbar i')
    this.$searchbar = $('#searchbar')
    this.initiated = true
    this.$result = $('#searchbar .result')
    this.$indexResult = $('#searchbar .index')
    this.$input = $('#searchbar input:first')
    this.$resultWrapper = $('#searchbar .result-wrapper')
    this.$morelink = $('#searchbar .more')

    if (serverData.user.token != undefined && serverData.user.token.length > 4) {
      var date = new Date()
      let tstring = '' + date.getYear() + '' + date.getMonth() + '' + date.getDate() + '' + date.getHours()
      $.getJSON('/cache/searchindex/' + serverData.user.token + '.json?t=' + tstring, function (data) {
        search.index = data
      })
    }

    this.$input.keyup(function () {
      if (search.index !== false && search.index.length > 0 && search.$input.val().length > 1) {
        search.indexSearch()
        search.$resultWrapper.show()
      }

      if (search.$input.val().length > 3 && search.$indexResult.children('li').length < 10) {
        search.start()
        search.$resultWrapper.show()
      } else if (search.$input.val().length == 0) {
        search.$result.html('')
        search.$indexResult.html('')
        search.$resultWrapper.hide()
      }
      $('#searchbar input:first').focus(function (e) {
        search.open()
      })
    })
  },
  open: function () {
    if (!this.initiated) {
      this.init()
    }
  },
  indexSearch: function () {
    search.$indexResult.html('')
    for (let i = 0; i < search.index.length; i++) {
      var hasTitle = false
      for (let y = 0; y < search.index[i].result.length; y++) {
        let check = false

        for (let x = 0; x < search.index[i].result[y].search.length; x++) {
          let parts = search.$input.val().split(' ')

          for (let z = 0; z < parts.length; z++) {
            let string = parts[z].trim().toLowerCase()

            if (
              string.length > 1 && !_.isNull(search.index[i].result[y].search[x]) &&
              search.index[i].result[y].search[x].toLowerCase().indexOf(string) >= 0
            ) {
              check = true
              x = (search.index[i].result[y].search.length + 1)
              z = (parts.length + 1)
            }
          }
        }
        if (check) {
          if (!hasTitle) {
            hasTitle = true
            search.$indexResult.append('<li class="title">' + search.index[i].title + '</li>')
          }
          let click = ''
          let href = '#'
          let img = ''
          if (search.index[i].result[y].click != undefined) {
            click = ' onclick="' + search.index[i].result[y].click + ';$(\'#searchbar .result-wrapper\').hide();return false;"'
          } else {
            href = search.index[i].result[y].href
          }
          if (search.index[i].result[y].img != undefined && search.index[i].result[y].img.length > 4) {
            img = '<span class="i"><img src="' + search.index[i].result[y].img + '" /></span>'
          }
          search.$indexResult.append(`<li class="corner-all"><a class="corner-all" href="${href}"${click}>${img}<span class="n">${search.index[i].result[y].name}</span><span class="t">${search.index[i].result[y].teaser}</span><span class="c"></span></li>`)
        }
      }
    }
  },
  showLoader: function () {
    this.$icon.removeClass('fa-search').addClass('fa-spin fa-circle-o-notch')
  },
  hideLoader: function () {
    this.$icon.removeClass('fa-spin fa-circle-o-notch').addClass('fa-search')
  },
  showResult: function (result) {
    search.$result.html('')
    for (let i = 0; i < result.length; i++) {
      search.$result.append('<li class="title">' + result[i].title + '</li>')
      for (let y = 0; y < result[i].result.length; y++) {
        search.$result.append('<li class="corner-all"><a class="corner-all" href="#" onclick="' + result[i].result[y].click + ';$(\'#searchbar .result-wrapper\').hide();return false;"><span class="n">' + result[i].result[y].name + '</span><span class="t">' + result[i].result[y].teaser + '</span></li>')
      }
    }
  },
  noResult: function () {
    search.$result.html('<li class="title">Kein Ergebnis</li>')
  },
  start: function () {
    this.showLoader()

    if (!this.isSearching) {
      this.isSearching = true
      this.lastSearch = search.$input.val()
      ajax.req('search', 'search', {
        loader: false,
        data: { s: this.lastSearch },
        success: function (data) {
          if (data != undefined && data.length > 0) {
            search.showResult(data)
          } else {
            search.noResult()
          }
        },
        complete: function () {
          search.hideLoader()
          setTimeout(function () {
            search.isSearching = false
            if (search.lastSearch != search.$input.val()) {
              search.start()
            }
          }, 300)
        }
      })
    }
  }
}

export default search
