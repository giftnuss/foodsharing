import '@/core'
import '@/globals'
import './Blog.css'
import '@/tablesorter'
import 'jquery.tinymce'
import 'jquery-jcrop'
import { GET } from '@/browser'
import { pictureCrop, pictureReady, ifconfirm } from '@/script'
import { expose } from '@/utils'
import { vueApply, vueRegister } from '@/vue'
import BlogOverview from './components/BlogOverview.vue'

expose({
  pictureCrop,
  pictureReady,
  ifconfirm,
})

if (GET('sub') === 'manage') {
  vueRegister({
    BlogOverview,
  })
  vueApply('#vue-blog-overview') // BlogOverview
}
