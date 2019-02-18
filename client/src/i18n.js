import serverData from '@/server-data'
import trans from '@translations/lang.de.yml'
import objectPath from 'object-path'

const { translations } = serverData

export default function (key, variables = {}) {
  let message = objectPath.get(trans, key)
  if (!message) message = translations[key]
  if (!message) throw new Error(`Missing translation for [${key}]`)
  return message.replace(/\{([^}]+)\}/g, (match, name) => {
    if (variables.hasOwnProperty(name)) {
      const value = variables[name]
      return value
    } else {
      throw new Error(`Variable [${name}] was not provided for [${key}]`)
    }
  })
}
