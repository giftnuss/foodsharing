const convert = require('koa-connect')
const proxy = require('http-proxy-middleware')

const webpackConfig = require('./webpack.config')

module.exports = {
  ...webpackConfig,
  serve: {
    content: [],
    dev: {
      publicPath: webpackConfig.output.publicPath,
      stats: 'minimal'
    },
    add: (app, middleware, options) => {
      middleware.webpack()
      app.use(convert(proxy('/', {
        target: 'http://localhost:18080',
        changeOrigin: true,
        ws: true
      })))
    }
  }
}
