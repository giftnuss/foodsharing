const path = require('path')
const clientRoot = path.resolve(__dirname)
const shims = require('./shims')

module.exports = {
  resolve: {
    extensions: ['.js'],
    modules: [
      resolve('node_modules')
    ],
    alias: {
      ...shims.alias,
      'fonts': resolve('../fonts'),
      'img': resolve('../img'),
      'css': resolve('../css'),
      'js': resolve('../js'),
      '@': resolve('src'),
      '@php': resolve('../src'),
      '>': resolve('test')
    }
  },
  module: {
    rules: [
      {
        test: /\.js$/,
        exclude: [
          /(node_modules)/,
          resolve('../js') // ignore the old js/**.js files
        ],
        use: 'babel-loader'
      },
      ...shims.rules
    ]
  }
}

function resolve (dir) {
  return path.join(clientRoot, dir)
}
