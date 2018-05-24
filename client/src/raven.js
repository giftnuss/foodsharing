import Raven from 'raven-js'

import serverData from '@/server-data'

if (serverData.ravenConfig) {
  console.log('using raven config from server', serverData.ravenConfig)
  Raven
    .config(serverData.ravenConfig, { tags: { webpack: true } })
    .install()
}
