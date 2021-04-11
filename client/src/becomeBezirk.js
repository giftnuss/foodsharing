/* eslint-disable eqeqeq,camelcase */

import $ from 'jquery'

import {
  pulseError,
  showLoader,
  hideLoader,
} from '@/script'

import {
  goTo,
} from '@/browser'

import 'jquery-ui'

import * as api from '@/api/regions'
import { listRegionChildren } from '@/api/regions'

$('#becomebezirkchooser-button').button().on('click', async function () {
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

      try {
        await api.joinRegion(bid)
        goTo(`/?page=relogin&url=${encodeURIComponent('/?page=bezirk&bid=' + $('#becomebezirkchooser').val())}`)
        $.fancybox.close()
      } catch (err) {
        pulseError('In diesen Bezirk kannst Du Dich nicht eintragen.')
      } finally {
        hideLoader()
      }
    } else {
      pulseError('In diesen Bezirk kannst Du Dich nicht eintragen.')
      return false
    }
  } else {
    pulseError('<p><strong>Du musst eine Auswahl treffen.</strong></p><p>Gibt es Deine Stadt, Deinen Bezirk oder Deine Region noch nicht, dann triff die passende übergeordnete Auswahl (also für Köln-Ehrenfeld z. B. Köln)!</p>')
  }
})

export async function u_printChildBezirke (element) {
  const val = `${element.value}`

  const part = val.split(':')

  const parent = part[0]

  if (parent == -1) {
    $('#becomebezirkchooser').val('')
    return false
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

  $(`#xv-childbezirk-${parent}`).remove()

  showLoader()
  const children = await listRegionChildren(parent)
  if (children !== undefined && children.length > 0) {
    $(`#becomebezirkchooser-childs-${parent}`).remove()

    let html = '<select class="select childChanger" id="xv-childbezirk-' + parent + '" onchange="u_printChildBezirke(this);">'
    html += '<option value="-1:0" class="xv-childs-0">Bitte auswählen...</option>'
    children.forEach(child => {
      html += '<option value="' + child.id + ':' + child.type + '" class="xv-childs-' + child.id + '">' + child.name + '</option>'
    })
    html += '</select>'
    $('#becomebezirkchooser-wrapper').append(html)
  }

  hideLoader()
}
