import { patch, post } from './base'

export function join (regionId) {
  return post(`/region/${regionId}/join`)
}

export function masterUpdate (regionId) {
  return patch(`/region/${regionId}/masterupdate`)
}
