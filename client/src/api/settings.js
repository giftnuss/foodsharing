import { patch } from './base'

export async function setProfilePhoto (uuid) {
  return await patch('/user/photo', {
    uuid: uuid,
  })
}
