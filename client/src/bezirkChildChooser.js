import $ from 'jquery'
import 'jquery-ui-addons'

import { showLoader, hideLoader } from '@/script'

$(() => printChildBezirke({ value: '0:0' }))

function printChildBezirke (element) {
  var val = element.value + ''

  var part = val.split(':')

  var parent = part[0]

  /*
if (parent == -1) {
  $("#{{ id }}").val("");
  return false;
}

if (parent == -2) {
  $("#{{ id }}-notAvail").fadeIn();
}

$("#{{ id }}").val(element.value);
*/

  var el = $(element)

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
      if (data.status === 1) {
        /*
        $("#{{ id }}-childs-" + parent).remove();
        $("#{{ id }}-wrapper").append(data.html);
        */
      } else {

      }
    },
    complete: function () {
      hideLoader()
    }
  })
}
