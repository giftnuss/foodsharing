import '@/core'
import '@/globals'
import './Blog.css'
import '@/tablesorter'
import 'jquery.tinymce'
import 'jquery-jcrop'
import { pictureCrop, pictureReady, ifconfirm } from '@/script'
import { expose } from '@/utils'

expose({
  pictureCrop,
  pictureReady,
  ifconfirm
})
