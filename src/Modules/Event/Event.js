import '@/core'
import '@/globals'
import './Event.css'
import { attachAddressPicker } from '@/addressPicker'
import { GET } from '@/browser'
// Wallpost
import '../WallPost/WallPost.css'
import { initWall } from '@/wall'

const sub = GET('sub')
if (sub === 'add' || sub === 'edit') {
  attachAddressPicker()
} else {
  initWall('event', GET('id'))
}
