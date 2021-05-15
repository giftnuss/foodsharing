import '@/core'
import '@/globals'
import { vueApply, vueRegister } from '@/vue'
import Index from './components/Index'

vueRegister({
  Index,
})
vueApply('#index')
