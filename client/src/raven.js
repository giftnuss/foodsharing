import Raven from 'raven-js'

if (process.env.RAVEN_CONFIG) {
  Raven
    .config(process.env.RAVEN_CONFIG)
    .install()
}
