import serverData from '@/server-data'
import trans from '@translations/lang.de.yml'
import objectPath from 'object-path'

const { translations } = serverData

export default function (key, variables = {}) {
  let message = objectPath.get(trans, key)
  if (!message) message = translations[key]
  if (!message) {
    console.error(new Error(`Missing translation for [${key}]`))
    return key
  }
  return message.replace(/{([^}]+)}/g, (match, name) => {
    if (Object.prototype.hasOwnProperty.call(variables, name)) {
      return variables[name]
    } else {
      throw new Error(`Variable [${name}] was not provided for [${key}]`)
    }
  })
}
