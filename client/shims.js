const path = require('path')
const clientRoot = path.resolve(__dirname)

function resolve (dir) {
  return path.join(clientRoot, dir)
}

function js (filename) {
  return resolve(path.join('..', 'js', filename))
}

exports.rules = [
  /*
  {
    // Makes jQuery available to non-webpack scripts
    // Does not seem to be working though :/
    test: require.resolve('jquery'),
    use: [
      'jQuery',
      '$',
    ].map(val => {
      return {
        loader: 'expose-loader',
        options: val
      }
    })
  },
  */
  {
    // test: /jquery/,
    test: require.resolve('jquery'),
    use: {
      loader: './debug-loader',
      options: { name: 'jquery' }
    }
  },
  {
    test: resolve('src/globals'),
    use: {
      loader: 'expose-loader',
      options: 'globals'
    }
  },

  // Basically specifying dependencies
  ...importLoadersRules({
    [require.resolve('fullpage.js')]: ['define=>false'],
    [require.resolve('jquery-slimscroll')]: ['jQuery=jquery'],
    [require.resolve('jquery-contextmenu')]: ['jQuery=jquery'],
    [require.resolve('timeago/jquery.timeago')]: ['define=>false', 'jQuery=jquery'],
    [js('jquery-ui-addons.js')]: ['jQuery=jquery', 'window.jQuery=jquery', '_=jquery-ui'],
    [js('jquery.popup.min.js')]: ['window.jQuery=jquery'],
    [js('fancybox/jquery.fancybox.pack.js')]: ['jQuery=jquery'],
    [js('jquery.animatenumber.min.js')]: ['jQuery=jquery']
  })
]

function importLoadersRules (entries) {
  return Object.keys(entries).map(lib => {
    const deps = entries[lib]
    return {
      test: lib,
      use: {
        loader: 'imports-loader',
        options: deps.join(',')
      }
    }
  })
}

exports.alias = {
  'jquery-ui-addons': js('jquery-ui-addons.js'),
  'jquery-tablesorter': js('tablesorter/jquery.tablesorter.min.js'),
  'jquery-fancybox': js('fancybox/jquery.fancybox.pack.js'),
  'jquery-jcrop': js('jquery.Jcrop.min.js'),
  'jquery-tagedit-auto-grow-input': js('tagedit/js/jquery.autoGrowInput.js'),
  'jquery-tagedit': js('tagedit/js/jquery.tagedit.js'),
  'jquery-animatenumber': js('jquery.animatenumber.min.js'),
  'autolink': js('autolink.js'),
  'underscore': js('underscore.js'),
  'underscore-string': js('underscore.string.js'),
  'instant-search': js('instant-search.js'),
  'jquery-popup': js('jquery.popup.min.js'),
  'typeahead': js('typeahead.bundle.js'),
  'typeahead-address-picker': js('typeahead-addresspicker.js'),
  'leaflet': js('leaflet/leaflet.js')
}
