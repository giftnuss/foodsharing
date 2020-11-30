import { remove } from './base'

export function deleteBlogpost (id) {
  return remove(`/blog/${id}`)
}
