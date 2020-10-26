import '@/core'
import '@/globals'
import i18n from '@/i18n'
import { vueApply, vueRegister } from '@/vue'
import Index from './components/Index'

vueRegister({
  Index,
})
vueApply('#index')

const video = document.querySelector('.vidlink')
video.addEventListener('click', (event) => {
  if (!confirm(i18n('index.confirm_open_video'))) {
    event.preventDefault()
  }
})
