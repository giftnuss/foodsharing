/* eslint-disable eqeqeq,camelcase */

import $ from 'jquery'

import {
  pulseError,
  goTo,
  showLoader,
  hideLoader
} from '@/script'

import 'jquery-ui'

const swapMsg = 'Welcher Bezirk soll neu angelegt werden?'

$('#becomebezirkchooser-notAvail').hide()

$('#becomebezirkchooser-btna').button().click(function () {
  $('#becomebezirkchooser-btna').fadeOut(200, function () {
    $('#becomebezirkchooser-notAvail').fadeIn()
  })
})

$('#becomebezirkchooser-button').button().click(function () {
  if (parseInt($('#becomebezirkchooser').val()) > 0) {
    const part = $('#becomebezirkchooser').val().split(':')

    if (part[1] == 5) {
      pulseError('Das ist ein Bundesland. Wähle bitte eine Stadt, eine Region oder einen Bezirk aus!')
      return false
    } else if (part[1] == 6) {
      pulseError('Das ist ein Land. Wähle bitte eine Stadt, eine Region oder einen Bezirk aus!')
      return false
    } else if (part[1] == 8) {
      pulseError('Das ist eine Großstadt. Wähle bitte eine Stadt, eine Region oder einen Bezirk aus!')
      return false
    } else if (part[1] == 1 || part[1] == 9 || part[1] == 2 || part[1] == 3) {
      const bid = part[0]
      showLoader()

      let neu = ''
      if ($('#becomebezirkchooser-neu').val() != swapMsg) {
        neu = $('#becomebezirkchooser-neu').val()
      }
      $.ajax({
        dataType: 'json',
        url: 'xhr.php?f=becomeBezirk',
        data: 'b=' + bid + '&new=' + neu,
        success: function (data) {
          if (data.status == 1) {
            goTo('/?page=relogin&url=' + encodeURIComponent('/?page=bezirk&bid=' + $('#becomebezirkchooser').val()))
            $.fancybox.close()
          }
          if (data.script != undefined) {
            $.globalEval(data.script)
          }
        },
        complete: function () {
          hideLoader()
        }
      })
    } else {
      pulseError('In diesen Bezirk kannst Du Dich nicht eintragen.')
      return false
    }
  } else {
    pulseError('<p><strong>Du musst eine Auswahl treffen.</strong></p><p>Gibt es Deine Stadt, Deinen Bezirk oder Deine Region noch nicht, dann triff die passende übergeordnete Auswahl</p><p>(also für Köln-Ehrenfeld z. B. Köln)</p><p>und schreibe die Region, welche neu angelegt werden soll, in das Feld unten!</p>')
  }
})

export function u_printChildBezirke (element) {
  const val = element.value + ''

  const part = val.split(':')

  var parent = part[0]

  if (parent == -1) {
    $('#becomebezirkchooser').val('')
    return false
  }

  if (parent == -2) {
    $('#becomebezirkchooser-notAvail').fadeIn()
  }

  $('#becomebezirkchooser').val(element.value)

  const el = $(element)

  if (el.next().next().next().next().next().hasClass('childChanger')) {
    el.next().next().next().next().next().remove()
  }
  if (el.next().next().next().next().hasClass('childChanger')) {
    el.next().next().next().next().remove()
  }
  if (el.next().next().next().hasClass('childChanger')) {
    el.next().next().next().remove()
  }
  if (el.next().next().hasClass('childChanger')) {
    el.next().next().remove()
  }
  if (el.next().hasClass('childChanger')) {
    el.next().remove()
  }

  $('#xv-childbezirk-' + parent).remove()

  showLoader()
  $.ajax({
    dataType: 'json',
    url: 'xhr.php?f=childBezirke&parent=' + parent,
    success: function (data) {
      if (data.status == 1) {
        $('#becomebezirkchooser-childs-' + parent).remove()
        $('#becomebezirkchooser-wrapper').append(data.html)
      } else {

      }
    },
    complete: function () {
      hideLoader()
    }
  })
}

u_printChildBezirke({value: '0:0'})