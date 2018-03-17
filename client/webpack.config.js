
const webpack = require('webpack')
const StatsWriterPlugin = require('webpack-stats-plugin').StatsWriterPlugin
const MiniCssExtractPlugin = require('mini-css-extract-plugin')
const path = require('path')
const clientRoot = path.resolve(__dirname)
const find = require('find')
const shims = require('./shims')
const { join } = require('path')

function resolve (dir) {
  return path.join(clientRoot, dir)
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
  entry: findModuleEntries(),
  mode: 'development',
  output: {
    path: resolve('../assets'),
    filename: 'js/[name].[hash].js', // TODO: JUST hash for prod, just name for dev
    publicPath: '/assets/'
  },
  resolve: {
    extensions: ['.js', '.vue'],
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
      '@php': resolve('../src')
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
        use: {
          loader: 'babel-loader',
          options: {
            presets: ['@babel/preset-env']
          }
        }
      },
      {
        test: /\.css$/,
        use: [
          MiniCssExtractPlugin.loader,
          'css-loader'
        ]
      },
      {
        test: /\.(png|jpe?g|gif|svg)(\?.*)?$/,
        loader: 'url-loader',
        options: {
          limit: 10000,
          name: 'img/[name].[hash:7].[ext]'
        }
      },
      {
        test: /\.(woff2?|eot|ttf|otf)(\?.*)?$/,
        loader: 'url-loader',
        options: {
          limit: 10000,
          name: 'fonts/[name].[hash:7].[ext]'
        }
      },
      ...shims.rules
    ]
  },
  plugins: [
    new webpack.ProvidePlugin({
      // ...shims.provides
    }),
    new MiniCssExtractPlugin({
      filename: '[name].css',
      chunkFilename: '[name].[hash].css'
    }),
    new StatsWriterPlugin({
      filename: 'modules.json',
      fields: ['publicPath', 'assetsByChunkName', 'entrypoints'],
      transform (stats) {
        const data = {}
        for (const [entryName, { assets }] of Object.entries(stats.entrypoints)) {
          data[entryName] = assets.map(asset => join(stats.publicPath, asset))
        }
        return JSON.stringify(data, null, 2)
      }
    })
  ],
  optimization: {
    splitChunks: {
      chunks: 'all',
      name: false
    },
    runtimeChunk: true
  }
}
