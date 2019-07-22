import '@/core'
import '@/globals'
import './Event.css'
import { attachAddressPicker } from '@/addressPicker'
import { GET } from '@/browser'

const sub = GET('sub')
if (sub === 'add' || sub === 'edit') {
  attachAddressPicker()
}
