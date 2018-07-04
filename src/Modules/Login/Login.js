import '@/core'
import '@/globals'
import { subPage } from '@/server-data'

if (subPage === 'index') {
  const isMobileInput = document.querySelector('#ismob')

  if (window.matchMedia('(max-width: 900px)').matches) {
    isMobileInput.value = 1
  } else {
    isMobileInput.value = 0
  }
}
