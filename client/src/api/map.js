import { get } from './base'

export function getMapMarkers (types, status) {
  const typeParams = types.map(t => 'types[]=' + t)
  const statusParams = status.map(t => 'status[]=' + t)
  const params = typeParams.concat(statusParams).join('&')
  return get(`/map/markers?${params}`)
}
