const webpack = require('webpack')

const Mocha = require('mocha')

const watch = process.argv.indexOf('--watch') !== -1

const compiler = webpack(require('../webpack.test.config'))

let mocha = new Mocha()

const setupFile = require.resolve('./setup-jsdom')

require(setupFile)

let runner
let runAgain = false

function run (fn) {
  const testFile = require.resolve('./_compiled.js')
  if (watch) console.clear()
  mocha.ui('bdd')
  mocha.grep(null)
  mocha.files = [testFile]
  runner = mocha.run((...args) => {
    if (fn) fn(...args)
    runner = null
    mocha.suite = mocha.suite.clone()
    mocha.suite.ctx = new Mocha.Context()
    delete require.cache[testFile]
    if (runAgain) {
      runAgain = false
      run()
    }
  })
}

function rerun (abortCurrent = false) {
  if (runner) {
    if (abortCurrent) {
      console.log('aborting existing run!')
      runner.abort()
    }
    runAgain = true
  } else {
    run()
  }
}

if (watch) {
  listenForKeypress()
  onEnter(rerun)
  compiler.watch({}, (err, stats) => {
    handleErrors(err, stats, false)
    rerun(true)
  })
} else {
  compiler.run((err, stats) => {
    handleErrors(err, stats, true)
    run(code => process.exit(code))
  })
}

function handleErrors (err, stats, exit) {
  if (err) {
    console.error(err.stack || err)
    if (err.details) {
      console.error(err.details)
    }
    if (exit) process.exit(1)
    return
  }

  if (stats.hasErrors() || stats.hasWarnings()) {
    console.log(stats.toString({
      chunks: false,
      colors: true
    }))
  }

  if (stats.hasErrors() && exit) process.exit(1)
}


function onEnter (fn) {
  process.stdin.on('keypress', (letter, key) => {
    if (key.ctrl && key.name === 'c') {
      process.exit()
    } else if (key.name === 'return' || key.name === 'enter') {
      fn()
    }
  })
}

function listenForKeypress () {
  process.stdin.setRawMode(true)
  require('readline').emitKeypressEvents(process.stdin)
}
