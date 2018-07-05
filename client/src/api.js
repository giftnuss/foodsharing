import 'whatwg-fetch'

import i18n from '@/i18n'
import { pulseError } from '@/script'

const prefix = '/api'

const fetchJSONWithCredentials = (endpoint) => {
  return window.fetch(endpoint, { credentials: 'same-origin' })
    .then(data => data.json())
    .catch(error => {
      pulseError(i18n('error_default'))
      return Promise.reject(error)
    })
}

export default {
  getConversations: (id) =>
    fetchJSONWithCredentials(`${prefix}/conversations/${id}`)
}
