import '@/core'
import '@/globals'

import './Index.css'

const video = document.querySelector('.vidlink')
const videoHref = video.getAttribute('href')
video.addEventListener('click', (event) => {
  event.preventDefault()
  video.innerHTML = `
    <iframe width="100%" height="315" src="${videoHref}" frameborder="0" allowfullscreen></iframe>
  `
});