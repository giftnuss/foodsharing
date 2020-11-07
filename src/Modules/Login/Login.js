import '@/core'
import '@/globals'
import './Login.css'
import { vueApply, vueRegister } from '@/vue'
import LoginForm from './components/LoginForm.vue'

vueRegister({
  LoginForm,
})
const selector = '#login-form'
const elements = document.querySelectorAll(selector)
if (Array.from(elements)?.length) {
  vueApply(selector)
}
