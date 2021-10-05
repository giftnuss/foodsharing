import { get, patch, post } from './base'

export function joinRegion (regionId) {
  return post(`/region/${regionId}/join`)
}

export function leaveRegion (regionId) {
  return post(`/region/${regionId}/leave`)
}

export function masterUpdate (regionId) {
  return patch(`/region/${regionId}/masterupdate`)
}

export function setRegionOptions (regionId, enableReportButton, enableMediationButton) {
  return post(`/region/${regionId}/options`, {
    enableReportButton: enableReportButton,
    enableMediationButton: enableMediationButton,
  })
}

export function setRegionPin (regionId, lat, lon, desc) {
  return post(`/region/${regionId}/pin`, {
    lat: lat,
    lon: lon,
    desc: desc,
  })
}

export function listRegionChildren (regionId) {
  return get(`/region/${regionId}/children`)
}
