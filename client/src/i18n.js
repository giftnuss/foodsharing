import serverData from '@/server-data'
import de from '@translations/messages.de.yml'
import en from '@translations/messages.en.yml'
import fr from '@translations/messages.fr.yml'
import it from '@translations/messages.it.yml'
import objectPath from 'object-path'

export const { locale } = serverData

export default function (key, variables = {}) {
  // find the selected language, use German as fallback
  const language = { en: en, fr: fr, it: it }
  const selected = Object.keys(language).find(l => l.localeCompare(locale || l) === 0)
  const src = selected ? language[selected] : de

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
