import $ from 'jquery'
import { format } from 'date-fns'

// wrapper around the legacy registration method
// should be replaced by a proper REST Endpoint `POST /api/user`

export async function register (data) {
  return new Promise((resolve, reject) => {
    const birthdateFormat = format(new Date(data.birthdate), 'yyyy-MM-dd')
    $.ajax({
      url: '/xhrapp.php?app=login&m=joinsubmit',
      data: {
        name: data.firstname,
        surname: data.lastname,
        email: data.email,
        pw: data.password,
        birthdate: birthdateFormat,
        mobile_phone: data.mobile,
        gender: data.gender,
        newsletter: data.subscribeNewsletter
      },
      dataType: 'json',
      method: 'POST',
      success: function (res) {
        console.log('success', res)
        if (res.status === 1) {
          resolve()
        } else {
          reject(new Error(res.error))
        }
      },
      fail: reject
    })
  })
}
