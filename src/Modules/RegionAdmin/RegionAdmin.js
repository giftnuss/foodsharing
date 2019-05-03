import '@/core'
import '@/globals'
import $ from 'jquery'
import i18n from '@/i18n'
import 'jquery-tagedit'
import 'jquery-tagedit-auto-grow-input'
import 'jquery-dynatree'
import 'leaflet'
import 'leaflet.awesome-markers'
import 'leaflet.markercluster'
import { goTo, img, pulseError, pulseSuccess } from '@/script'
import { expose } from '@/utils'
import './RegionAdmin.css'
import { deleteGroup } from '@/api/groups'

expose({
  img,
  deleteActiveGroup
})

async function deleteActiveGroup () {
  const groupName = $('#tree-hidden-name').val()
  const groupId = $('#tree-hidden').val()
  if (window.confirm(i18n('group.delete_group_sure', { groupName }))) {
    try {
      await deleteGroup(groupId)
      pulseSuccess(i18n('success'))
      goTo('/?page=region')
    } catch (err) {
      pulseError(i18n('error_unexpected'))
      throw err
    }
  }
}
