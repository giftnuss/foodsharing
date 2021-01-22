import { get, post } from './base'

export async function getReportsByRegion (regionId) {
  return (await get(`/report/region/${regionId}`)).data
}

export function addReport (reportedId, reporterId, reasonId, reason, message, storeId) {
  return post('/report', {
    reportedId: reportedId,
    reporterId: reporterId,
    reasonId: reasonId,
    reason: reason,
    message: message,
    storeId: storeId,
  })
}
