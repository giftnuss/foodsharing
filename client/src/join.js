import $ from 'jquery'
import {
  pulseError,
  pulseInfo
} from '@/script'
import { initializeMap } from '@php/Lib/View/vMap'
import { expose } from '@/utils'

var join = {
  currentStep: 0,
  googleApiKey: null,
  markerIcon: null,
  marker: null,
  isLoading: false,
  mapInitialized: false,
  init: function () {
    this.mapInitialized = false
  },
  photoUploadError: function (error) {
    pulseError(error)
    join.isLoading = false
    $('#joinform .avatar form').removeClass('load')
    $('#join_avatar_error').val('1')
  },
  readyUpload: function (name) {
    $('#joinform .avatar .container').html('').css({
      'background-image': `url(/tmp/${name})`
    })
    join.isLoading = false
    $('#joinform .avatar form').removeClass('load')
    $('#join_avatar').val(name)
  },
  startUpload: function () {
    $('#join_photoform').addClass('load').trigger('submit')
    join.isLoading = true
    $('#joinform .avatar .container').css('background-image', 'none').html('<span class="fas fa-camera-retro"></span><span class="fas fa-circle-notch fa-spin"></span>')
  },
  loadMap: function () {
    if (!this.mapInitialized) {
      const mapEL = document.getElementById('map')
      if (mapEL) {
        initializeMap(mapEL, (result) => {
          let prop = result.properties
          let geo = result.geometry.coordinates
          $('#join_lat').val(geo[1])
          $('#join_lon').val(geo[0])
          $('#join_plz').val(prop.postcode)
          $('#join_ort').val(prop.city)
          $('#join_str').val(prop.street)
          $('#join_hsnr').val(prop.housenumber)
          $('#join_country').val(prop.country)
        })
      }
      this.mapInitialized = true
    }
  },
  finish: function () {
    if ($('#join_legal1:checked').length <= 0) {
      pulseError('Bitte akzeptiere unsere Datenschutzerkl&auml;rung')
      return false
    } else if ($('#join_legal2:checked').length <= 0) {
      pulseError('Bitte akzeptiere unsere Rechtsvereinbarung')
      return false
    } else {
      $('#joinform').hide()
      $('#joinloader').show()

      $.ajax({
        url: '/xhrapp.php?app=login&m=joinsubmit',
        type: 'post',
        dataType: 'json',
        data: {
          iam: $('#join_iam').val(),
          name: $('#login_name').val(),
          surname: $('#login_surname').val(),
          email: $('#login_email').val(),
          pw: $('#login_passwd1').val(),
          birthdate: $('#birthdate').val(),
          avatar: $('#join_avatar').val(),
          mobile_phone: $('#login_mobile_phone').val(),
          lat: $('#join_lat').val(),
          lon: $('#join_lon').val(),
          str: $('#join_str').val(),
          nr: $('#join_hsnr').val(),
          plz: $('#join_plz').val(),
          city: $('#join_ort').val(),
          gender: $('#login_gender').val(),
          country: $('#join_country').val(),
          newsletter: ($('#newsletter').prop('checked')) ? 1 : 0
        },
        success: function (ret) {
          if (ret.status !== undefined && ret.status === 1) {
            $('#joinloader').hide()
            $('#joinready').show()
          } else if (ret.status !== undefined && ret.status === 0) {
            pulseError(ret.error)
            $('#joinloader').hide()
            $('#joinform').show()
            join.step(1)
          }
        }
      })
    }
  },
  step: function (step) {
    if (join.currentStep >= step || join.stepCheck(step)) {
      $('.step').hide()
      $(`.step${step}`).show()
      $('.linklist.join li').removeClass('active').children('a').children('i').remove()
      $(`.linklist.join li.step${step}`).addClass('active')
      $(`.linklist.join li.step${step}`).removeClass('hidden').children('a').append('<i class="far hand-point-right"></i>')

      if (step === 2) join.loadMap()

      join.currentStep = step
    }
  },
  stepCheck: function (step) {
    switch (join.currentStep) {
      case 1:
        // trim whitespace from email for validation and submission
        $('#login_email').val($('#login_email').val().trim())

        if ($('#login_name').val() === '') {
          pulseInfo('Bitte Gib einen Benutzernamen ein')
          $('#login_name').trigger('select')
          return false
        }
        if (!$('#login_email')['0'].validity.valid) {
          pulseError('Mit Deiner E-Mail-Adresse stimmt etwas nicht')
          $('#login_email').trigger('select')
          return false
        }
        let birthdate = new Date($('#birthdate').val())
        let now = new Date()
        let diff = now.getFullYear() - birthdate.getFullYear()
        if (birthdate.getMonth() > now.getMonth()) {
          diff--
        } else {
          if (birthdate.getMonth() === now.getMonth()) {
            if (birthdate.getDay() > now.getDay()) { diff-- }
          }
        }
        if (diff < 18) {
          pulseInfo('Aus datenschutz- und haftungsrechtlichen Gründen musst du mindestens 18 Jahre alt sein, um bei foodsharing.de mitzumachen.')
          return false
        }

        if ($('#login_passwd1').val().length < 4) {
          pulseInfo('Dein Passwort muss länger als 4 Buchstaben sein')
          $('#login_passwd1').trigger('select')
          return false
        }

        if ($('#login_passwd1').val() !== $('#login_passwd2').val()) {
          pulseInfo('Deine Passwörter stimmen nicht überein')
          $('#login_passwd1').trigger('select')
          return false
        }

        if (join.isLoading) {
          pulseInfo('Bitte warte bis Dein Foto hochgeladen ist')
          return false
        }

        if ($('#join_avatar_error').val() === '0' && $('#join_avatar').val() === '') {
          if (!window.confirm('Du hast kein Foto hochgeladen. Beachte, dass ein passbildähnliches Foto benötigt wird, wenn du später auch als Foodsaver aktiv werden möchtest. Ohne Foto fortfahren?')) {
            return false
          }
        }
        return true

      default:
        return true
    }
  }
}

expose({
  join
})
