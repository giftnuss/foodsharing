export function jsonOK (data) {
  return [200, { 'Content-Type': 'application/json' }, JSON.stringify(data)]
}

export function sleep (ms) {
  return new Promise(resolve => setTimeout(resolve, ms))
}
