import $ from 'jquery'
import {
  pulseError
} from '@/script'
import { expose } from '@/utils'

var join = {
  finish: function () {
    $.ajax({
      url: '/xhrapp.php?app=login&m=joinsubmit',
      type: 'post',
      dataType: 'json',
      data: {
        name: $('#login_name').val(),
        surname: $('#login_surname').val(),
        email: $('#login_email').val(),
        pw: $('#login_passwd1').val(),
        birthdate: $('#birthdate').val(),
        avatar: $('#join_avatar').val(),
        mobile_phone: $('#login_mobile_phone').val(),
        gender: $('#login_gender').val(),
        newsletter: ($('#newsletter').prop('checked')) ? 1 : 0
      },
      success: function (ret) {
        if (ret.status !== undefined && ret.status === 1) {
        } else if (ret.status !== undefined && ret.status === 0) {
          pulseError(ret.error)
        }
      }
    })
  }
}

expose({
  join
})
