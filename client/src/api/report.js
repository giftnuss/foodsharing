import { get } from './base'

export async function getReportsByRegion (regionId) {
  return (await get(`/report/region/${regionId}`)).data
}
