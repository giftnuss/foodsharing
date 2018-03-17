export function nice (dbtime) {
  // has time?
  if (dbtime.indexOf(':') > 0) {
    return niceDateTime(dbtime)
  } else {
    return niceDate(dbtime)
  }
}

export function niceDateTime (dbtime) {
  let parts = dbtime.split(' ')
  const time = parts[1]
  const date = parts[0]

  parts = date.split('-')
  let out = parts[2] + '.' + parts[1] + '.' + parts[0] + ' '

  parts = time.split(':')
  return out + parts[0] + '.' + parts[1] + ' Uhr'
}

export function niceDate (dbtime) {
  const parts = dbtime.split('-')

  return parts[2] + '.' + parts[1] + '.' + parts[0]
}
