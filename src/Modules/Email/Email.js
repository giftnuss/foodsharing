import '@/core'
import '@/globals'
import 'jquery-dynatree'
import 'jquery.tinymce'
import $ from 'jquery'
import i18n from '@/i18n'
import { pulseInfo, pulseError } from '@/script'
import { expose } from '@/utils'
import { sendTestEmail } from '@/api/newsletter'

expose({ trySendTestEmail })

async function trySendTestEmail () {
  try {
    await sendTestEmail($('#testemail').val(), $('#subject').val(), $('#message').tinymce().getContent())
    pulseInfo(i18n('newsletter.test_email_sent'))
  } catch (err) {
    pulseError(i18n('newsletter.test_email_invalid'))
  }
}
