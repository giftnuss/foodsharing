import serverData from '@/server-data'

const { translations } = serverData

export default function (key, variables = {}) {
  const message = translations[key]
  if (!message) throw new Error(`Missing translation for [${key}]`)
  return message.replace(/\{([^}]+)\}/g, (match, name) => {
    const value = variables[name]
    if (!value) throw new Error(`Variable [${name}] was not provided for [${key}]`)
    return value
  })
}
