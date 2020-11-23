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
import 'mapbox-gl-leaflet'
import 'mapbox-gl/dist/mapbox-gl.css'
import { initMap } from '@/mapUtils'
import { goTo, img, pulseError, pulseSuccess } from '@/script'
import { expose } from '@/utils'
import './RegionAdmin.css'
import { deleteGroup } from '@/api/groups'
import { masterUpdate } from '@/api/regions'
import { searchUser } from '@/api/search'

expose({
  img,
  deleteActiveGroup,
  initMap,
  tryMasterUpdate,
  searchUser,
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
      if (err.code === 409) {
        pulseError(i18n('region.still_contains_elements'))
      } else {
        pulseError(i18n('error_unexpected'))
      }
      throw err
    }
  }
}

async function tryMasterUpdate (regionId) {
  try {
    masterUpdate(regionId)
    pulseSuccess(i18n('success'))
  } catch (e) {
    pulseError(i18n('error_unexpected'))
  }
}
