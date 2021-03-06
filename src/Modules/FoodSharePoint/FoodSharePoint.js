/* eslint-disable camelcase */
import '@/core'
import '@/globals'
import 'jquery-tagedit'
import 'jquery-tagedit-auto-grow-input'
import 'jquery-jcrop'
import { attachAddressPicker } from '@/addressPicker'
import { vueApply, vueRegister } from '@/vue'
import FileUploadVForm from '@/components/upload/FileUploadVForm'
import { GET } from '@/browser'

import './FoodSharePoint.css'

// Wallpost
import '../WallPost/WallPost.css'
import { initWall } from '@/wall'

vueRegister({
  FileUploadVForm,
})

const sub = GET('sub')
if (sub === 'add' || sub === 'edit') {
  attachAddressPicker()
  vueApply('#image-upload')
} else if (sub === 'ft') {
  initWall('fairteiler', GET('id'))
}
