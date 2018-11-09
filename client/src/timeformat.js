
const timeformat = {

  nice (dbtime) {
    // has time?
    if (dbtime.indexOf(':') > 0) {
      return timeformat.niceDateTime(dbtime)
    } else {
      return timeformat.niceDate(dbtime)
    }
  },

  niceDateTime (dbtime) {
    let parts = dbtime.split(' ')
    const time = parts[1]
    const date = parts[0]

    parts = date.split('-')
    let out = `${parts[2]}.${parts[1]}.${parts[0]} `

    parts = time.split(':')
    return `${out + parts[0]}.${parts[1]} Uhr`
  },

  niceDate (dbtime) {
    const parts = dbtime.split('-')

    return `${parts[2]}.${parts[1]}.${parts[0]}`
  }

}

export default timeformat
