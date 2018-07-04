const mkdirp = require('mkdirp')
const merge = require('webpack-merge')
const webpackBase = require('./webpack.base')
const { writeFileSync } = require('fs')
const { BundleAnalyzerPlugin } = require('webpack-bundle-analyzer')
const MiniCssExtractPlugin = require('mini-css-extract-plugin')
const UglifyJsPlugin = require('uglifyjs-webpack-plugin')
const path = require('path')
const clientRoot = path.resolve(__dirname)
const { join } = require('path')
const modules = require('./modules')

const dev = process.env.NODE_ENV !== 'production'

const assetsPath = resolve('../assets')
const modulesJsonPath = join(assetsPath, 'modules.json')

const plugins = []

if (!dev) {
  plugins.push(
    new MiniCssExtractPlugin({
      filename: dev ? 'css/[name].css' : 'css/[id].[hash].css',
      chunkFilename: 'css/[id].[hash].css'
    }),
    new BundleAnalyzerPlugin({
      analyzerMode: 'static',
      reportFilename: 'bundlesize.html',
      defaultSizes: 'gzip',
      openAnalyzer: false,
      generateStatsFile: false,
      statsFilename: 'stats.json',
      statsOptions: null,
      logLevel: 'info'
    })
  )
}

plugins.push(
  {
    // Writes modules.json which is then loaded by the php app (see src/Modules/Core/Control.php).
    // This is how the php app will know if it is a webpack-enabled module or not.
    apply (compiler) {
      compiler.hooks.emit.tapPromise('write-modules', compiler => {
        let stats = compiler.getStats().toJson()
        const data = {}
        for (const [entryName, { assets }] of Object.entries(stats.entrypoints)) {
          data[entryName] = assets.map(asset => join(stats.publicPath, asset))
        }
        // We do not emit the data like a proper plugin as we want to create the file when running the dev server too
        const json = JSON.stringify(data, null, 2) + '\n'
        mkdirp.sync(assetsPath)
        writeFileSync(modulesJsonPath, json)
        return Promise.resolve()
      })
    }
  }
)

module.exports = merge(webpackBase, {
  entry: moduleEntries(...modules),
  mode: dev ? 'development' : 'production',
  devtool: dev ? 'cheap-module-eval-source-map' : 'source-map',
  stats: 'minimal',
  output: {
    path: assetsPath,
    ...(dev ? {
      filename: 'js/[name].js',
      chunkFilename: 'js/[chunkhash].js'
    } : {
      filename: 'js/[name].[hash].js',
      chunkFilename: 'js/[id].[chunkhash].js'
    }),
    publicPath: '/assets/',

    // See https://github.com/ctrlplusb/react-universally/pull/566#issuecomment-373292166
    // TODO: find somewhere to set the multiStep option from https://github.com/webpack/webpack/issues/6693
    hotUpdateChunkFilename: '[hash].hot-update.js'
  },
  module: {
    rules: [
      {
        test: /\.css$/,
        use: [
          dev ? 'style-loader' : MiniCssExtractPlugin.loader,
          {
            loader: 'css-loader',
            options: {
              alias: {
                './img': ('img')
              }
            }
          }
        ]
      },
      {
        test: /\.(png|jpe?g|gif|svg)(\?.*)?$/,
        loader: 'url-loader',
        options: {
          limit: 10000,
          name: dev ? 'img/[name].[ext]' : 'img/[name].[hash:7].[ext]'
        }
      },
      {
        test: /\.(woff2?|eot|ttf|otf)(\?.*)?$/,
        loader: 'url-loader',
        options: {
          limit: 10000,
          name: dev ? 'fonts/[name].[ext]' : 'fonts/[name].[hash:7].[ext]'
        }
      }
    ]
  },
  plugins,
  optimization: {
    minimizer: [
      new UglifyJsPlugin({
        uglifyOptions: {
          safari10: true
        }
      })
    ],
    splitChunks: {
      chunks: 'all',
      name: dev
    }
  }
})

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
