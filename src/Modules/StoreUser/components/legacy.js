/* eslint-disable eqeqeq */
import $ from 'jquery'

import { showLoader, hideLoader } from '@/script'

export async function legacyXhrCall (func, fdata = null) {
  showLoader()
  return $.ajax({
    dataType: 'json',
    url: `/xhr.php?f=${func}`,
    data: fdata || {},
    success: function (data) {
      hideLoader()
      if (data.status == 1) {
        hideLoader()
      }
    },
    complete: function () {
      hideLoader()
    },
  })
}
