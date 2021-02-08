import Vue from 'vue'
import * as Sentry from '@sentry/vue'
import serverData from '@/server-data'

if (serverData.ravenConfig) {
  console.log('using sentry config from server', serverData.ravenConfig)
  Sentry.init({
    Vue: Vue,
    dsn: serverData.ravenConfig,
  })
}
