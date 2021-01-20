import { get, post } from './base'

export async function getReportsByRegion (regionId) {
  return (await get(`/report/region/${regionId}`)).data
}

export function addReport (reportedId, reporterID, reasonID, reason, message, storeID) {
  return post('/report/', {
    reportedId: reportedId,
    reporterID: reporterID,
    reasonID: reasonID,
    reason: reason,
    message: message,
    storeID: storeID,
  })
}
