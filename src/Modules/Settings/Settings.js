/* eslint-disable camelcase */
import '@/core'
import '@/globals'
import './Settings.css'
import 'jquery-jcrop'
import 'jquery-dynatree'
import { attachAddressPicker } from '@/addressPicker'
import {
  fotoupload,
  picFinish,
  pulseSuccess,
  pulseError,
  goTo,
  collapse_wrapper,
  GET
} from '@/script'
import { expose } from '@/utils'
import i18n from '@/i18n'
import { deleteUser } from '@/api/user'

expose({
  fotoupload,
  picFinish,
  collapse_wrapper,
  confirmDeleteAccount
})

if (GET('sub') === 'general') {
  attachAddressPicker()
}

async function confirmDeleteAccount (fsId) {
  if (window.confirm(i18n('foodsaver.delete_account_sure'))) {
    try {
      await deleteUser(fsId)
      pulseSuccess(i18n('success'))
      goTo('/')
    } catch (err) {
      pulseError(i18n('error_unexpected'))
      throw err
    }
  }
}
