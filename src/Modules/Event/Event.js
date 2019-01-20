import '@/core'
import '@/globals'
import './Event.css'
import { attachAddresspicker } from '@/addresspicker'
import { GET } from '@/browser'

let sub = GET('sub')
if (sub === 'add' || sub === 'edit') {
  attachAddresspicker()
}
