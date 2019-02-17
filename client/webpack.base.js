const path = require('path')
const clientRoot = path.resolve(__dirname)
const shims = require('./shims')
const MiniCssExtractPlugin = require('mini-css-extract-plugin')
const VueLoaderPlugin = require('vue-loader/lib/plugin')
const WriteFilePlugin = require('write-file-webpack-plugin')

const plugins = [
  new VueLoaderPlugin(),
  new WriteFilePlugin() // to write files to filesystem when using webpack-dev-server
]

const production = process.env.NODE_ENV === 'production'

if (production) {
  plugins.push(
    new MiniCssExtractPlugin({
      filename: 'css/[id].[hash].css',
      chunkFilename: 'css/[id].[hash].css'
    })
  )
}

module.exports = {
  resolve: {
    extensions: ['.js', '.vue'],
    modules: [
      resolve('node_modules')
    ],
    alias: {
      ...shims.alias,
      'fonts': resolve('../fonts'),
      'img': resolve('../img'),
      './img': resolve('../img'),
      'css': resolve('../css'),
      'js': resolve('lib'),
      '@': resolve('src'),
      '@php': resolve('../src'),
      '>': resolve('test'),
      '@translations': resolve('../lang'),
      '@b': resolve('node_modules/bootstrap-vue/es')
    }
  },
  module: {
    rules: [
      {
        test: /\.js$/,
        exclude: [
          /(node_modules)/,
          resolve('lib') // ignore the old lib/**.js files
        ],
        use: {
          loader: 'babel-loader',
          options: {
            'presets': [
              [
                '@babel/preset-env',
                {
                  'targets': {
                    'browsers': ['> 0.5%', 'ie_mob >=11']
                  },
                  'useBuiltIns': 'usage',
                  'modules': 'commonjs'
                }
              ]
            ]
          }
        }
      },
      {
        test: /\.vue$/,
        exclude: /(node_modules)/,
        use: 'vue-loader'
      },
      {
        test: /\.css$/,
        use: [
          production ? MiniCssExtractPlugin.loader : 'style-loader',
          {
            loader: 'css-loader'
          }
        ]
      },
      {
        test: /\.scss$/,
        use: [
          production ? MiniCssExtractPlugin.loader : 'style-loader',
          'css-loader',
          'sass-loader'
        ]
      },
      {
        test: /\.yml$/,
        exclude: [
          /(node_modules)/
        ],
        use: [
          'json-loader',
          'yaml-loader'
        ]
      },
      ...shims.rules
    ]
  },
  plugins
}

function resolve (dir) {
  return path.join(clientRoot, dir)
}
