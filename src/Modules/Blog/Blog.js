import '@/core'
import '@/globals'
import './Blog.css'
import '@/tablesorter'
import 'jquery.tinymce'
import 'jquery-jcrop'
import { GET } from '@/browser'
import { ifconfirm } from '@/script'
import { expose } from '@/utils'
import { vueApply, vueRegister } from '@/vue'
import BlogOverview from './components/BlogOverview.vue'
import FileUploadVForm from '@/components/upload/FileUploadVForm'

expose({
  ifconfirm,
})

if (GET('sub') === 'manage') {
  vueRegister({
    BlogOverview,
  })
  vueApply('#vue-blog-overview') // BlogOverview
} else if (GET('sub') === 'add' || GET('sub') === 'edit') {
  vueRegister({
    FileUploadVForm,
  })
  vueApply('#image-upload')
}
