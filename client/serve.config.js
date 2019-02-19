const webpackConfig = require('./webpack.config')

const target = process.env.PROXY_TARGET || 'http://localhost:18080'
const host = process.env.HOST || '127.0.0.1'

module.exports = {
  ...webpackConfig,
  devServer: {
    host,
    port: 18080,
    hot: true,
    index: '',
    contentBase: false,
    publicPath: '/assets/',
    proxy: {
      '!/sockjs-node/**': {
        target,
        changeOrigin: true,
        xfwd: true,
        ws: true
      }
    }
  }
}
