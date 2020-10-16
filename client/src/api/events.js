import { ajreq } from '@/script'
// import { patch, remove } from './base'

export function acceptInvitation (eventId, userId) {
  return ajreq('eventresponse', { app: 'event', id: eventId, s: 1 }) // ACCEPTED
  // return patch(`/event/${eventId}/response/${userId}`, { certain: true })
}

export function maybeInvitation (eventId, userId) {
  return ajreq('eventresponse', { app: 'event', id: eventId, s: 2 }) // MAYBE
  // return patch(`/event/${eventId}/response/${userId}`, { certain: false })
}

export function declineInvitation (eventId, userId) {
  return ajreq('eventresponse', { app: 'event', id: eventId, s: 3 }) // WONT_JOIN
  // return remove(`/event/${eventId}/response/${userId}`)
}
