/* eslint-disable eqeqeq */
import '@/core'
import '@/globals'
import $ from 'jquery'
import 'jquery-tagedit'
import 'jquery-tagedit-auto-grow-input'
import Cropper from 'cropperjs'
import 'cropperjs/dist/cropper.css'
import './WorkGroup.css'

const $groups = $('.groups .field')
if ($groups.length > 3) {
  $groups.children('.head').css({
    'cursor': 'pointer',
    'margin-bottom': '10px'
  }).mouseover(function () {
    $(this).css('text-decoration', 'underline')
  }).mouseout(function () {
    $(this).css('text-decoration', 'none')
  }).click(function () {
    const $this = $(this)

    if (!$this.next('.ui-widget.ui-widget-content.corner-bottom').is(':visible')) {
      $groups.children('.ui-widget.ui-widget-content.corner-bottom').hide()

      $groups.children('.head').css({
        'margin-bottom': '10px'
      })

      $this.css({
        'margin-bottom': '0px'
      }).next('.ui-widget.ui-widget-content.corner-bottom').show()
    } else {
      $this.css('margin-bottom', '10px')
      $groups.children('.ui-widget.ui-widget-content.corner-bottom').hide()
    }
  })

  $groups.children('.ui-widget.ui-widget-content.corner-bottom').hide()
}

const selectEl = $('#work_group_form_applyType')

function handleApplicationConstraintVisibility () {
  if (selectEl.val() == 1) {
    $('#addapply').show()
  } else {
    $('#addapply').hide()
  }
}

selectEl.change(handleApplicationConstraintVisibility)
handleApplicationConstraintVisibility()

const onAfterClose = []
let cropper = null

$('#work_group_form_photo-link').fancybox({
  autoSize: true,
  maxWidth: '90%',
  scrolling: 'auto',
  closeClick: false,
  beforeClose: () => {
    onAfterClose.forEach(fn => fn())
    console.log('afterclose')
  },
  afterLoad: () => {
    const image = document.getElementById('work_group_form_photo-image')
    const input = document.getElementById('work_group_form_photo-upload')
    const upload = document.getElementById('work_group_form_photo-save')
    const rotate = document.getElementById('work_group_form_photo-rotate')
    const target = document.getElementById('work_group_form_photo-preview')
    const formTarget = document.getElementById('work_group_form_photo')
    const uploadError = document.getElementById('work_group_form_photo-upload-error')
    const cropperOptions = {
      viewMode: 1,
      autoCrop: true
    }
    if (cropper === null) {
      cropper = new Cropper(image, cropperOptions)
    }

    const onRotateClick = () => {
      cropper.rotate(90)
    }
    rotate.addEventListener('click', onRotateClick)
    onAfterClose.push(() => rotate.removeEventListener('click', onRotateClick))

    const onFileChange = e => {
      const files = e.target.files
      const done = function (url) {
        input.value = ''
        image.src = url
        cropper.replace(url)
      }
      if (files && files.length > 0) {
        const file = files[0]
        if (window.URL) {
          done(window.URL.createObjectURL(file))
        } else if (window.FileReader) {
          const reader = new window.FileReader()
          reader.onload = e => {
            done(reader.result)
          }
          reader.readAsDataURL(file)
        }
      }
    }
    input.addEventListener('change', onFileChange)
    onAfterClose.push(() => input.removeEventListener('change', onFileChange))

    const onUploadClick = () => {
      cropper.getCroppedCanvas({
        width: 500,
        heigth: 500,
        maxWidth: 2000,
        maxHeight: 2000,
        fillColor: '#ffffff',
        imageSmoothingQuality: 'high'
      }).toBlob((blob) => {
        const formData = new window.FormData()
        formData.append('image', blob)
        uploadError.innerHTML = 'Uploading...'
        $.ajax('/xhr.php?f=uploadPictureRefactorMeSoon', {
          method: 'POST',
          data: formData,
          processData: false,
          contentType: false,
          success: (data) => {
            uploadError.innerHTML = 'Done'
            target.src = data.fullPath
            formTarget.value = data.fullPath
            $.fancybox.close()
          },
          error: (data) => {
            uploadError.innerHTML = 'Upload failed: ' + data
          }
        })
      })
    }
    upload.addEventListener('click', onUploadClick)
    onAfterClose.push(() => upload.removeEventListener('click', onUploadClick))
  },
  helpers: {
    overlay: { closeClick: false }
  }
})

$('#work_group_form_photo-opener').button().click(function () {
  $('#work_group_form_photo-link').trigger('click')
})

const tageditOptions = {
  autocompleteURL: 'xhr.php?f=getRecip',
  allowEdit: false,
  allowAdd: false,
  animSpeed: 100
}

$('#work_group_form_members input.tag').tagedit(tageditOptions)

$('#work_group_form_administrators input.tag').tagedit(tageditOptions)

$('.fancybox').fancybox()
