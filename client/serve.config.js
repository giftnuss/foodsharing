const webpackConfig = require('./webpack.config')

const target = process.env.PROXY_TARGET || 'http://localhost:18080'
const host = process.env.HOST || '127.0.0.1'

module.exports = {
  ...webpackConfig,
  devServer: {
    host: host,
    hot: true,
    index: '',
    contentBase: false,
    publicPath: '/assets/',
    proxy: {
      // proxy would by default also affect HMR
      '!/sockjs-node/**': {
        target: target,
        changeOrigin: true,
        ws: true,
        onProxyReq (proxyReq, req, res) {
          proxyReq.setHeader('use-dev-assets', 'true')
        }
      }
    }
  }
}
