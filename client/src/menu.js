import $ from 'jquery'
import { goTo, showLoader } from '@/script'
import { vueRegister, vueApply } from '@/vue'
import Topbar from '@/components/Topbar/index'

vueRegister({ Topbar })
vueApply('#vue-topbar')

$('#mobilemenu').bind('change', function () {
  if ($(this).val() !== '') {
    showLoader()
    goTo($(this).val())
  }
})

$('#top .menu').css('display', 'block')
