/* eslint-disable camelcase */
import '@/core'
import '@/globals'
import $ from 'jquery'
import {
  checkEmail,
  pulseError,
  ajax,
  goTo
} from '@/script'
import './Team.css'

let $form = $('#contactform-form')
if ($form.length > 0) {
  var $email = $('#email')

  $email.on('keyup', function () {
    var $el = $(this)
    if (checkEmail($el.val())) {
      $email.removeClass('input-error')
    }
  })

  $email.on('blur', function () {
    var $el = $(this)
    if (!checkEmail($el.val())) {
      $email.addClass('input-error')
      pulseError('Mit Deiner E-Mail-Adressse stimmt etwas nicht.')
    }
  })

  $form.on('submit', function (ev) {
    ev.preventDefault()
    if (!checkEmail($email.val())) {
      $email.trigger('select')
      $email.addClass('input-error')
      pulseError('Bitte gib eine gültige E-Mail-Adresse ein, damit wir Dir antworten können!')
    } else {
      ajax.req('team', 'contact', {
        data: $form.serialize(),
        method: 'post'
      })
    }
  })
}

let $teamList = $('#team-list')
$teamList.find('.foot i').on('mouseover', function () {
  var $this = $(this)

  var val = $this.children('span').text()
  if (val !== '') {
    $this.parent().parent().attr('href', val).attr('target', '_blank')
  }
})

$teamList.find('.foot i').on('click', function (ev) {
  var $this = $(this)
  if ($this.hasClass('fa-lock')) {
    ev.preventDefault()
  }

  if ($this.hasClass('fa-envelope')) {
    ev.preventDefault()
    goTo($this.parent().parent().attr('href'))
  }
})

$teamList.find('.foot i').on('mouseout', function () {
  var $this = $(this).parent().parent()

  $this.attr('href', `/team/${$this.attr('id').substring(2)}`).attr('target', '_self')
})
