const convert = require('koa-connect')
const proxy = require('http-proxy-middleware')

const webpackConfig = require('./webpack.config')

const target = process.env.PROXY_TARGET || 'http://localhost:18080'
const host = process.env.HOST || '127.0.0.1'

module.exports = {
  ...webpackConfig,
  serve: {
    host,
    clipboard: false,
    content: [],
    dev: {
      publicPath: webpackConfig.output.publicPath,
      stats: 'minimal'
    },
    add: (app, middleware, options) => {
      middleware.webpack()
      app.use(convert(proxy('/', {
        target,
        changeOrigin: true,
        ws: true,
        onProxyReq (proxyReq, req, res) {
          proxyReq.setHeader('use-dev-assets', 'true')
        }
      })))
    }
  }
}
