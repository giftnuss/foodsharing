import { post } from './base'

export async function uploadFile (filename, body) {
  return post('/uploads', {
    filename: filename,
    body: body,
  })
}
