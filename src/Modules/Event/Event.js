import '@/core'
import '@/globals'
import './Event.css'
import { attachAddressPicker } from '@/addressPicker'
import { GET } from '@/browser'

let sub = GET('sub')
if (sub === 'add' || sub === 'edit') {
  attachAddressPicker()
}
