import { patch, post } from './base'

export function join (regionId) {
  return post(`/region/${regionId}/join`)
}

export function leave (regionId) {
  return post(`/region/${regionId}/leave`)
}

export function masterUpdate (regionId) {
  return patch(`/region/${regionId}/masterupdate`)
}
