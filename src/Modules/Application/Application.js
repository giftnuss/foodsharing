import '@/core'
import '@/globals'
import { acceptApplication } from '@/api/applications'
import { pulseError, pulseInfo, goTo } from '@/script'
import i18n from '@/i18n'
import { expose } from '@/utils'

expose({
  tryAcceptApplication,
})

async function tryAcceptApplication (groupId, userId) {
  try {
    await acceptApplication(groupId, userId)
    pulseInfo(i18n('group.apply.accepted'))
    goTo(`/?page=bezirk&bid=${groupId}`)
  } catch (e) {
    pulseError(i18n('error_unexpected'))
  }
}
