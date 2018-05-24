const path = require('path')
const clientRoot = path.resolve(__dirname)

function resolve (dir) {
  return path.join(clientRoot, dir)
}

function lib (filename) {
  return resolve(path.join('..', 'js', filename))
}

exports.rules = [
  // Specifying dependencies for "legacy" libraries that don't/can't specify any themselves
  ...importLoadersRules({
    [require.resolve('fullpage.js')]: ['define=>false'],
    [require.resolve('jquery-slimscroll')]: ['jQuery=jquery'],
    [require.resolve('jquery-contextmenu')]: ['jQuery=jquery'],
    [require.resolve('timeago/jquery.timeago')]: ['define=>false', 'jQuery=jquery'],
    [lib('jquery-ui-addons.js')]: ['jQuery=jquery', 'window.jQuery=jquery', '_=jquery-ui'],
    [lib('fancybox/jquery.fancybox.pack.js')]: ['jQuery=jquery'],
    [lib('jquery.animatenumber.min.js')]: ['jQuery=jquery']
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
  'jquery-ui-addons': lib('jquery-ui-addons.js'),
  'jquery-tablesorter': lib('tablesorter/jquery.tablesorter.min.js'),
  'jquery-fancybox': lib('fancybox/jquery.fancybox.pack.js'),
  'jquery-jcrop': lib('jquery.Jcrop.min.js'),
  'jquery-tagedit-auto-grow-input': lib('tagedit/js/jquery.autoGrowInput.js'),
  'jquery-tagedit': lib('tagedit/js/jquery.tagedit.js'),
  'jquery-animatenumber': lib('jquery.animatenumber.min.js'),
  'autolink': lib('autolink.js'),
  'underscore': lib('underscore.js'),
  'underscore-string': lib('underscore.string.js'),
  'instant-search': lib('instant-search.js'),
  'typeahead': lib('typeahead.bundle.js'),
  'typeahead-address-picker': lib('typeahead-addresspicker.js'),
  'leaflet': lib('leaflet/leaflet.js')
}
