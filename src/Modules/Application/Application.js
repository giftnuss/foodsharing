import '@/core'
import '@/globals'
import { acceptApplication, declineApplication } from '@/api/applications'
import { pulseError, pulseInfo, goTo } from '@/script'
import i18n from '@/i18n'
import { expose } from '@/utils'

expose({
  tryAcceptApplication,
  tryDeclineApplication,
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

async function tryDeclineApplication (groupId, userId) {
  try {
    await declineApplication(groupId, userId)
    pulseInfo(i18n('group.apply.declined'))
    goTo(`/?page=bezirk&bid=${groupId}`)
  } catch (e) {
    pulseError(i18n('error_unexpected'))
  }
}
