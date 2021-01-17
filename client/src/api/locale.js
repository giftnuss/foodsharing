import { get, post } from './base'

export async function getLocale () {
  return (await get('/locale')).locale
}

export function setLocale (locale) {
  return post('/locale', {
    locale: locale,
  })
}
