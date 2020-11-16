/* eslint-disable eqeqeq,camelcase */

import $ from 'jquery'
import _ from 'underscore'

import 'jquery-slimscroll'
import 'jquery-fancybox'
import 'jquery-ui-addons'

import { GET, goTo, isMob } from '@/browser'
import conv from '@/conv'
import { removeStoreRequest } from '@/api/stores'
import i18n from '@/i18n'

export { goTo, isMob, GET }

export function collapse_wrapper (id) {
  const $content = $(`#${id}-wrapper .element-wrapper`)
  const $label = $(`#${id}-wrapper .wrapper-label i`)
  if ($content.is(':visible')) {
    $content.hide()
    $label.removeClass('fa-caret-down').addClass('fa-caret-right')
  } else {
    $content.show()
    $label.removeClass('fa-caret-right').addClass('fa-caret-down')
  }
}

function initSleepmode () {
  $('.sleepmode-1, .sleepmode-2').on('mouseover', function () {
    const tooltip = i18n('settings.sleep.tooltip', { name: $(this).text() })
    $(this).append(`<span class="corner-all bubble bubble-right ui-shadow">${tooltip}</span>`)
  })
  $('.sleepmode-1, .sleepmode-2').on('mouseout', function () {
    $(this).children('.bubble').remove()
  })
}

export function initialize () {
  $(function () {
    initSleepmode()

    $('textarea.comment').autosize()
    $('#main').css('display', 'block')

    $('.truncate-content').each(function () {
      const $this = $(this)
      let max_height

      if (!isMob() && $this.hasClass('collapse-mobile')) {
        return // skip + continue
      }

      const cheight = $this.attr('class').split('truncate-height-')
      if (cheight.length > 1) {
        max_height = parseInt(cheight[1])
      } else {
        max_height = 100
      }

      // only modify the element if it exceeds the defined max height
      // if it does, or if we're lazy-loading content expected to be large
      // => automatically collapse, and show button for expanding content
      if (($this.height() > max_height) || $this.hasClass('force-collapse')) {
        $this.css({
          height: `${max_height}px`,
          overflow: 'hidden',
        })
        $this.after(`<a class="expand-collapse-link" href="#" data-show="0" data-maxheight="${max_height}">
            <i class="fas fa-plus-square"></i>
            <span>mehr anzeigen</span>
        </a>`)
      }
    })

    $('.expand-collapse-link').each(function () {
      const $link = $(this)
      const $wrapper = $link.prev()

      $link.on('click', function (ev) {
        ev.preventDefault()
        if ($link.attr('data-show') == 0) {
          // currently collapsed => expand, and convert button to "collapse on click"
          $wrapper.css({
            height: 'auto',
            overflow: 'visible',
          })
          $link.children('i.fas').removeClass('fa-plus-square').addClass('fa-minus-square')
          $link.children('span').text('einklappen')
          $link.attr('data-show', 1)
        } else {
          // currently expanded => collapse, and convert button to "expand on click"
          const max_height = $link.attr('data-maxheight')
          $wrapper.css({
            height: `${max_height}px`,
            overflow: 'hidden',
          })
          $link.children('i.fas').removeClass('fa-minus-square').addClass('fa-plus-square')
          $link.children('span').text('mehr anzeigen')
          $link.attr('data-show', 0)
        }
      })
    })

    if (!isMob()) {
      $('#main a').tooltip({
        show: false,
        hide: false,
        content: function () {
          return $(this).attr('title')
        },
        position: {
          my: 'center bottom-20',
          at: 'center top',
          using: function (position, feedback) {
            $(this).css(position)
            $('<div>')
              .addClass('arrow')
              .addClass(feedback.vertical)
              .addClass(feedback.horizontal)
              .appendTo(this)
          },
        },
      })
    }

    $(function () {
      $('#dialog-confirm').dialog({
        resizable: false,
        height: 140,
        modal: true,
        autoOpen: false,
        buttons: {
          [i18n('button.permadelete')]: function () {
            goTo($('#dialog-confirm-url').val())
            $(this).dialog('close')
          },
          [i18n('button.cancel')]: function () {
            $(this).dialog('close')
          },
        },
      })
    })
    $('.dialog').dialog()

    $('ul.toolbar li').on('mouseenter', function () {
      $(this).addClass('ui-state-hover')
    }).on('mouseleave', function () {
      $(this).removeClass('ui-state-hover')
    },
    )

    $('.text, .textarea, select').on('focus',
      function () {
        $(this).addClass('focus')
      },
    )
    $('.text, .textarea, select').on('blur',
      function () {
        $(this).removeClass('focus')
      },
    )

    $('.value').on('blur', function () {
      const el = $(this)
      if (el.val() != '') {
        el.removeClass('input-error')
      }
    })

    $('#uploadPhoto').dialog({
      autoOpen: false,
      modal: true,
      buttons:
        {
          Upload: function () {
            uploadPhoto()
          },
        },
    })
  })
}

export function chat (fsid) {
  conv.userChat(fsid)
}

export function profile (id) {
  showLoader()
  goTo(`/profile/${id}`)
}

export const ajax = {
  data: {},
  msg: function (msg) {
    for (let i = 0; i < msg.length; i++) {
      switch (msg[i].type) {
        case 'error':
          pulseError(msg[i].text)
          break

        case 'success':
          pulseSuccess(msg[i].text)
          break

        default:
          pulseInfo(msg[i].text)
          break
      }
    }
  },
  req: function (app, method, option) {
    var opt = {}
    if (option != undefined) {
      opt = option
    }

    if (opt.method == undefined) {
      opt.method = 'get'
    }

    if (opt.loader == undefined || opt.loader == true) {
      opt.loader = true
      showLoader()
    }

    if (opt.data == undefined) {
      opt.data = {}
    }

    return $.ajax({
      url: `/xhrapp.php?app=${app}&m=${method}`,
      data: opt.data,
      dataType: 'json',
      method: opt.method,
      success: function (ret) {
        if (ret.status == 1) {
          if (ret.msg != undefined) {
            ajax.msg(ret.msg)
          }

          if (ret.append != undefined) {
            $(ret.append).html(ret.html)
          }

          if (ret.script != undefined) {
            if (ret.data != undefined) {
              ajax.data = ret.data
            }
            $.globalEval(ret.script)
          }

          if (opt.success != undefined) {
            opt.success(ret.data)
          }
        }
      },
      fail: function (request) {
        if (request.status === 403) {
          pulseError('Du hast leider nicht die notwendigen Berechtigungen für diesen Vorgang.')
        }
      },
      complete: function () {
        if (opt.loader === true) {
          hideLoader()
        }
        if (opt.complete != undefined) {
          opt.complete()
        }
      },
    })
  },
}
export function ajreq (name, options, method, app) {
  options = typeof options !== 'undefined' ? options : {}
  return ajax.req(options.app || app || GET('page'), name, {
    method: method,
    data: options,
    loader: options.loader,
  })
}

function definePulse (type, defaultTimeout = 5000) {
  return (html, options = {}) => {
    let { timeout, sticky } = options || {}
    if (typeof timeout === 'undefined') timeout = sticky ? 900000 : defaultTimeout
    const animationDuration = Math.min(timeout, 400)
    const el = $(`#pulse-${type}`)
    el.html(html).stop().fadeIn(animationDuration)
    const hide = () => {
      el.stop().fadeOut(animationDuration)
      $(document).off('click', hide)
      clearTimeout(timer)
    }
    const timer = setTimeout(hide, timeout)
    setTimeout(() => {
      $(document).on('click', hide)
    }, 500)
  }
}

export const pulseInfo = definePulse('info', 4000)
export const pulseSuccess = definePulse('success', 5000)
export const pulseError = definePulse('error', 6000)

export function checkEmail (email) {
  var filter = /^([a-zA-Z0-9_.-])+@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/

  if (!filter.test(email)) {
    return false
  } else {
    return true
  }
}
export function img (photo, size) {
  if (size == undefined) {
    size = 'med'
  }
  if (photo && photo.length > 3) {
    return `/images/${size}_q_${photo}`
  } else {
    return `/img/${size}_q_avatar.png`
  }
}

export function reload () {
  window.location.reload()
}

export function uploadPhoto () {
  $('#uploadPhoto form').trigger('submit')
}

export function uploadPhotoReady (id, file) {
  $(`#miniq-${id}`).attr('src', file)
  $('#uploadPhoto').dialog('close')
  pulseInfo('Foto erfolgreich hochgeladen!')
}

export function ifconfirm (url, question, title) {
  if (question != undefined) {
    $('#dialog-confirm-msg').html(question)
  }
  if (title != undefined) {
    $('#dialog-confirm').dialog('option', 'title', title)
  }

  $('#dialog-confirm-url').val(url)
  $('#dialog-confirm').dialog('open')
}

export function picFinish (img, id) {
  $(`#${id}-action`).val('upload')
  $.fancybox.close()
  const d = new Date()
  const imgp = `${img}?${d.getTime()}`
  $(`#${id}-open`).html(`<img src="images/${imgp}" /><input type="hidden" name="photo" value="${img}" />`)
  hideLoader()
  reload()
}
export function pic_error (msg, id) {
  msg = `<div class="ui-widget"><div style="padding: 15px;" class="ui-state-error ui-corner-all"><p><span style="float: left; margin-right: .3em;" class="ui-icon ui-icon-alert"></span><strong>Fehler:</strong> ${msg}</p></div></div>`
  $(`#${id}-placeholder`).html(msg)
  hideLoader()
}
export function fotoupload (file, id) {
  $(`#${id}-file`).val(file)
  const d = new Date()
  const img = `${file}?${d.getTime()}`

  $(`#${id}-placeholder`).html(`<img src="./tmp/${img}" />`)
  $(`#${id}-placeholder img`).Jcrop({
    setSelect: [100, 0, 400, 400],
    aspectRatio: 35 / 45,
    onSelect: function (c) {
      $(`#${id}-x`).val(c.x)
      $(`#${id}-y`).val(c.y)
      $(`#${id}-w`).val(c.w)
      $(`#${id}-h`).val(c.h)
    },
  })
  $(`#${id}-save`).show()
  $(`#${id}-save`).button().on('click', function () {
    showLoader()
    $(`#${id}-action`).val('crop')
    $(`#${id}-form`)[0].submit()
    return false
  })

  $(`#${id}-placeholder`).css('height', 'auto')
  hideLoader()
  setTimeout(function () {
    $.fancybox.update()
    $.fancybox.reposition()
    $.fancybox.toggle()
  }, 200)
}

export function closeBox () {
  $.fancybox.close()
}

export function pictureReady (id, img) {
  $(`#${id}-preview`).html(`<img src="images/${id}/thumb_${img}" />`)
  $(`#${id}`).val(`${id}/${img}`)

  $.fancybox.close()
  hideLoader()
}

export function pictureCrop (id, img) {
  const ratio = $.parseJSON($(`#${id}-ratio`).val())
  const ratio_val = $.parseJSON($(`#${id}-ratio-val`).val())

  const ratio_i = parseInt($(`#${id}-ratio-i`).val())

  if (ratio[ratio_i] != undefined) {
    $(`#${id}-ratio-i`).val((ratio_i + 1))
    $(`#${id}-crop`).html(`<img src="images/${id}/${img}" /><br /><span id="${id}-crop-save">Speichern</span>`)
    $(`#${id}-crop img`).Jcrop({
      setSelect: [100, 0, 400, 400],
      aspectRatio: ratio[ratio_i],
      onSelect: function (c) {
        $(`#${id}-x`).val(c.x)
        $(`#${id}-y`).val(c.y)
        $(`#${id}-w`).val(c.w)
        $(`#${id}-h`).val(c.h)
      },
    })
    hideLoader()
    setTimeout(function () {
      $.fancybox.update()
      $.fancybox.reposition()
      $.fancybox.toggle()
    }, 200)

    $(`#${id}-crop-save`).button().on('click', function () {
      ratio_val[ratio_val.length] = {
        x: Math.round($(`#${id}-x`).val()),
        y: Math.round($('#' + id + '-y').val()),
        w: Math.round($('#' + id + '-w').val()),
        h: Math.round($('#' + id + '-h').val()),
      }
      $(`#${id}-ratio-val`).val(JSON.stringify(ratio_val))

      pictureCrop(id, img)
    })
  } else {
    showLoader()
    $(`#${id}-form`).attr('action', `/xhr.php?f=pictureCrop&id=${id}&img=${img}`)
    $(`#${id}-form`).trigger('submit')
  }
}

export function u_loadCoords (addressdata, func) {
  let anschrift = ''
  if (addressdata.str != undefined) {
    anschrift = `${addressdata.str} ${addressdata.hsnr}`
  } else {
    const tmp = addressdata.anschrift.split('/')
    anschrift = tmp[0]
  }
  const address = encodeURIComponent(`${anschrift}, ${addressdata.plz}, ${addressdata.stadt}, Germany`)

  const url = `https://search.mapzen.com/v1/search?text=${address}`

  showLoader()
  $(function () {
    $.getJSON(url,
      function (data) {
        if (data.features) {
          for (let i = 0; i < data.features.length; i++) {
            if (data.features[i].properties.postalcode == addressdata.plz) {
              $('#pulse-error').hide()
              hideLoader()
              func(data.features[i].geometry.coordinates[0], data.features[i].geometry.coordinates[1])
              return true
            }
          }
        }

        hideLoader()

        pulseError('<strong>Die Koordinaten konnten nicht berechnet werden</strong><br />sind alle Eingaben Richtig? Ohne Koordinaten wird die Adresse nicht auf der Karte zu sehen sein')
      })
  })
}

export function showLoader () {
  $.fancybox.showLoading()
}
export function hideLoader () {
  $.fancybox.hideLoading()
}

export function betriebRequest (id) {
  showLoader()
  $.ajax({
    url: '/xhr.php?f=betriebRequest',
    data: { id: id },
    dataType: 'json',
    success: function (data) {
      if (data.status == 1) {
        pulseInfo(data.msg)
      }
    },
    complete: function () {
      hideLoader()
    },
  })
}

export async function withdrawStoreRequest (fsid, bid) {
  showLoader()

  try {
    await removeStoreRequest(bid, fsid)
    pulseSuccess(i18n('store.request.withdrawn'))
  } catch (e) {
    pulseError(i18n('error_unexpected'))
  }

  hideLoader()
}

export function checkAllCb (sel) {
  $("input[type='checkbox']").prop('checked', sel)
}

export function becomeBezirk () {
  $('#becomeBezirk-link').fancybox({
    minWidth: 390,
    maxWidth: 400,
  })
  $('#becomeBezirk-link').trigger('click')
}

export function shuffle (o) {
  for (var j, x, i = o.length; i; j = Math.floor(Math.random() * i), x = o[--i], o[i] = o[j], o[j] = x);
  return o
}

/**
 * Merges two object-like arrays based on a key property and also merges its array-like attributes specified in objectPropertiesToMerge.
 * It also removes falsy values after merging object properties.
 *
 * @param firstArray The original object-like array.
 * @param secondArray An object-like array to add to the firstArray.
 * @param keyProperty The object property that will be used to check if objects from different arrays are the same or not.
 * @param objectPropertiesToMerge The list of object properties that you want to merge. It all must be arrays.
 * @returns The updated original array.
 */
export function merge (firstArray, secondArray, keyProperty, objectPropertiesToMerge) {
  function mergeObjectProperties (object, otherObject, objectPropertiesToMerge) {
    _.each(objectPropertiesToMerge, function (eachProperty) {
      object[eachProperty] = _.chain(object[eachProperty]).union(otherObject[eachProperty]).compact().value()
    })
  }

  if (firstArray.length === 0) {
    _.each(secondArray, function (each) {
      firstArray.push(each)
    })
  } else {
    _.each(secondArray, function (itemFromSecond) {
      var itemFromFirst = _.find(firstArray, function (item) {
        return item[keyProperty] === itemFromSecond[keyProperty]
      })

      if (itemFromFirst) {
        mergeObjectProperties(itemFromFirst, itemFromSecond, objectPropertiesToMerge)
      } else {
        firstArray.push(itemFromSecond)
      }
    })
  }

  return firstArray
}

export function session_id () {
  return /SESS\w*ID=([^;]+)/i.test(document.cookie) ? RegExp.$1 : false
}

$.fn.extend({
  disableSelection: function () {
    return this.each(function () {
      this.onselectstart = function () { return false }
      this.unselectable = 'on'
      $(this).css('user-select', 'none')
    })
  },
})
