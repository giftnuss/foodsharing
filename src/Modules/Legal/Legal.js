import '@/core'
import '@/globals'
import $ from 'jquery'
import i18n from '@/i18n'

const noticeId = '#legal_form_privacyNoticeAcknowledged'
const $form = $('form[name="legal_form"]')
const doNotAgree = '0'

$form.submit(function (event) {
  if ($(noticeId) && $(noticeId).val() === doNotAgree) {
    if (!confirm(i18n('legal.are_you_sure_to_downgrade'))) {
      event.preventDefault()
    }
  }
})
