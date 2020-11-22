import { get, put, remove } from './base'

export async function getApiToken () {
  return (await get('/calendar/token')).token
}

export async function createApiToken () {
  return (await put('/calendar/token')).token
}

export async function removeApiToken () {
  return await remove('/calendar/token')
}
