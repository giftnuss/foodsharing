import serverData from '@/server-data'
import de from '@translations/messages.de.yml'
import en from '@translations/messages.en.yml'
import fr from '@translations/messages.fr.yml'
import objectPath from 'object-path'

export const { locale } = serverData

export default function (key, variables = {}) {
  let src = de
  if ('en'.localeCompare(locale || 'en') === 0) {
    src = en
  } else if ('fr'.localeCompare(locale || 'fr') === 0) {
    src = fr
  }

  let message = objectPath.get(src, key)
  if (!message) {
    message = objectPath.get(de, key)
  }
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
