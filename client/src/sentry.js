import Vue from 'vue'
import * as Sentry from '@sentry/browser'
import * as Integrations from '@sentry/integrations'
import serverData from '@/server-data'

if (serverData.ravenConfig) {
  console.log('using sentry config from server', serverData.ravenConfig)
  Sentry.init({
    dsn: serverData.ravenConfig,
    integrations: [new Integrations.Vue({ Vue, attachProps: true, logErrors: true })]
  })
}
