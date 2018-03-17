
const webpack = require('webpack')
const path = require('path')
const clientRoot = path.resolve(__dirname)
const find = require('find')
const shims = require('./shims')

function resolve (dir) {
  return path.join(clientRoot, dir)
}

function findSnippetEntries () {
  const entries = {}
  const dir = resolve('src/snippets')
  find.fileSync(/\.js$/, dir).forEach(filename => {
    const entryName = filename.substring(dir.length + 1).replace(/\.js$/, '')
    entries['snippets/' + entryName] = filename
  })
  return entries
}

function findModuleEntries () {
  const entries = {}
  const dir = resolve('../src/Modules')
  find.fileSync(/\.js$/, dir).forEach(filename => {
    const entryName = path.basename(filename.replace(/\.js$/, ''))
    entries['Modules/' + entryName] = filename
  })
  return entries
}

module.exports = {
  entry: {
    // main: resolve('src/main'),
    // ...findSnippetEntries(),
    ...findModuleEntries(),
    // ...shims.entry
  },
  output: {
    path: resolve('../js/gen/webpack'),
    filename: 'js/[name].js',
    chunkFilename: 'js/chunks/[id].[chunkhash].js',
    publicPath: 'js/gen/webpack/'
  },
  resolve: {
    extensions: ['.js', '.vue'],
    modules: [
      resolve('node_modules')
    ],
    alias: {
      ...shims.alias,
      '@': resolve('src'),
      '@php': resolve('../src')
    }
  },
  module: {
    rules: [
      {
        // Load client/src and src/modules/**.js via babel, but not js/**.js
        test: /\.js$/,
        exclude: [
          /(node_modules)/,
          resolve('../js')
        ],
        use: {
          loader: 'babel-loader',
          options: {
            presets: ['@babel/preset-env']
          }
        }
      },
      ...shims.rules
    ]
  },
  plugins: [
    new webpack.ProvidePlugin({
      ...shims.provides
    })
  ]
  /*
  optimization: {
    splitChunks: {
      chunks: 'all'
    }
  }
  */
}
