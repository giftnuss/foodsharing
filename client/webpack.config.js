const { StatsWriterPlugin } = require('webpack-stats-plugin')
const { BundleAnalyzerPlugin } = require('webpack-bundle-analyzer')
const MiniCssExtractPlugin = require('mini-css-extract-plugin')
const path = require('path')
const clientRoot = path.resolve(__dirname)
const shims = require('./shims')
const { join } = require('path')

function resolve (dir) {
  return path.join(clientRoot, dir)
}

function moduleEntries (...names) {
  const entries = {}
  for (const name of names) {
    entries[`Modules/${name}`] = resolve(`../src/Modules/${name}/${name}.js`)
  }
  return entries
}

module.exports = {
  entry: moduleEntries(
    // We explicitly define each foodsharing modules here so we can convert them one-by-one
    'Index',
    'Dashboard'
  ),
  devtool: 'source-map',
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
    new MiniCssExtractPlugin({
      filename: 'css/[name].css',
      chunkFilename: 'css/[name].[hash].css'
    }),
    new BundleAnalyzerPlugin(), // TODO only in prod

    // Writes modules.json which is then loaded by the php app (see src/Modules/Core/Control.php).
    // This is how the php app will know if it is a webpack-enabled module or not.
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
