import { get, post, put, remove } from './base'
import { formatISO } from 'date-fns'

export async function getPoll (pollId) {
  return get(`/polls/${pollId}`)
}

export async function listPolls (groupId) {
  return get(`/groups/${groupId}/polls`)
}

export function createPoll (regionId, name, description, startDate, endDate, scope, type, options, notifyVoters) {
  return post('/polls', {
    regionId: regionId,
    name: name,
    description: description,
    startDate: formatISO(startDate),
    endDate: formatISO(endDate),
    scope: scope,
    type: type,
    options: options,
    notifyVoters: notifyVoters
  })
}

export async function deletePoll (pollId) {
  return remove(`/polls/${pollId}`)
}

export async function vote (pollId, options) {
  return put(`/polls/${pollId}/vote`, {
    options: options
  })
}
