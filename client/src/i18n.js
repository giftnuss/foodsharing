import serverData from '@/server-data'
import de from '@translations/messages.de.yml'
import en from '@translations/messages.en.yml'
import objectPath from 'object-path'

const { translations, locale } = serverData

export default function (key, variables = {}) {
  const src = 'en'.localeCompare(locale) === 0 ? en : de
  let message = objectPath.get(src, key)
  if (!message) message = objectPath.get(de, key)
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
