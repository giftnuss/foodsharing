const glob = require('glob')
const merge = require('webpack-merge')

const path = require('path')
const clientRoot = path.resolve(__dirname)
const shims = require('./shims')

const webpackBase = require('./webpack.base')

module.exports = merge(webpackBase, {
  entry: glob.sync(resolve('src/**/*.test.js')),
  mode: 'development',
  devtool: 'inline-cheap-module-source-map',
  output: {
    path: resolve('test'),
    filename: '_compiled.js'
  },
  target: 'node',
  module: {
    rules: [
      {
        test: /\.css$/,
        use: [
          'style-loader',
          'css-loader'
        ]
      },
      ...shims.rules
    ]
  }
})

function resolve (dir) {
  return path.join(clientRoot, dir)
}
