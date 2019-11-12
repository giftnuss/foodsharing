import '@/core'
import '@/globals'
import i18n from '@/i18n'

import './Index.scss'

const video = document.querySelector('.vidlink')
video.addEventListener('click', (event) => {
  if (!confirm(i18n('index.confirm_open_video'))) {
    event.preventDefault()
  }
})
