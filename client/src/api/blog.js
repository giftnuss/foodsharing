import { patch, remove } from './base'

export async function publishBlogpost (blogId, newPublishedState) {
  return patch(`/blog/${blogId}`, { isPublished: +newPublishedState })
}

export async function deleteBlogpost (blogId) {
  return remove(`/blog/${blogId}`)
}
