import { post } from './base'

export function join (regionId) {
  return post(`/region/${regionId}/join`)
}
