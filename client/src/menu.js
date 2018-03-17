import $ from 'jquery'
import { goTo, showLoader } from '@/script'

$('#mobilemenu').bind('change', function () {
  if ($(this).val() !== '') {
    showLoader()
    goTo($(this).val())
  }
})

$('#top .menu').css('display', 'block')
