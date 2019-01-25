/* eslint-disable eqeqeq,camelcase */
import $ from 'jquery'
import i18n from '@/i18n'
import { expose } from '@/utils'

expose({ u_delPost, mb_finishImage })

export function u_delPost (postId, module, wallId) {
  $.ajax({
    url: `/xhrapp.php?app=wallpost&m=delpost&table=${module}&id=${wallId}&post=${postId}`,
    dataType: 'JSON',
    success: function (data) {
      if (data.status == 1) {
        $(`.wallpost-${postId}`).remove()
      }
    }
  })
}

export function mb_finishImage (file) {
  $('#wallpost-attach').append(`<input type="hidden" name="attach[]" value="image-${file}" />`)
  $('#attach-preview div:last').remove()
  $('.attach-load').remove()
  $('#attach-preview').append(`<a rel="wallpost-gallery" class="preview-thumb" href="images/wallpost/${file}"><img src="images/wallpost/thumb_${file}" height="60" /></a>`)
  $('#attach-preview').append('<div style="clear:both;"></div>')
  $('#attach-preview a').fancybox()
  mb_clear()
}

function mb_clear () {
  $('#wallpost-loader').html('')
  $('a.attach-load').remove()
}

export function init (module, wallId) {
  $('#wallpost-text').autosize()
  $('#wallpost-text').on('focus', function () {
    $('#wallpost-submit').show()
  })

  $('#wallpost-attach-trigger').change(function () {
    $('#attach-preview div:last').remove()
    $('#attach-preview').append('<a rel="wallpost-gallery" class="preview-thumb attach-load" href="#" onclick="return false;">&nbsp;</a>')
    $('#attach-preview').append('<div style="clear:both;"></div>')
    $('#wallpost-attachimage-form').submit()
  })

  $('#wallpost-text').blur(function () {
    $('#wallpost-submit').show()
  })
  $('#wallpost-post').submit(function (ev) {
    ev.preventDefault()
  })
  $('#wallpost-attach-image').button().click(function () {
    $('#wallpost-attach-trigger').click()
  })
  $('#wall-submit').button().click(function (ev) {
    ev.preventDefault()
    if (($('#wallpost-text').val() != '' && $('#wallpost-text').val() != i18n('wall.message_placeholder')) || $('#attach-preview a').length > 0) {
      $('.wall-posts table tr:first').before('<tr><td colspan="2" class="load">&nbsp;</td></tr>')

      let attach = ''
      $('#wallpost-attach input').each(function () {
        attach = `${attach}:${$(this).val()}`
      })
      if (attach.length > 0) {
        attach = attach.substring(1)
      }

      let text = $('#wallpost-text').val()
      if (text == i18n('wall.message_placeholder')) {
        text = ''
      }

      $.ajax({
        url: `/xhrapp.php?app=wallpost&m=post&table=${module}&id=${wallId}`,
        type:
          'POST',
        data:
          {
            text: text,
            attach: attach
          },
        dataType: 'JSON',
        success:
          function (data) {
            $('#wallpost-attach').html('')
            if (data.status == 1) {
              $('.wall-posts').html(data.html)
              $('.preview-thumb').fancybox()
              if (data.script != undefined) {
                $.globalEval(data.script)
              }
            }
          }
      })

      $('#wallpost-text').val('')
      $('#attach-preview').html('')
      $('#wallpost-attach').html('')
      $('#wallpost-text')[0].trigger('focus')
      $('#wallpost-text').css('height', '33px')
    }
  })
  $('#wallpost-attach-trigger').on('focus', function () {
    $('#wall-submit')[0].trigger('focus')
  })
  $.ajax({
    url: `/xhrapp.php?app=wallpost&m=update&table=${module}&id=${wallId}&last=0`,
    dataType:
      'JSON',
    success:

      function (data) {
        if (data.status == 1) {
          $('.wall-posts').html(data.html)
          $('.preview-thumb').fancybox()
        }
      }
  })
}
