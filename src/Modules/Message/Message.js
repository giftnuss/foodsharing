import core from '@/core'
import globals from '@/globals'
import 'jquery-tagedit'
import 'jquery-tagedit-auto-grow-input'
import './Message.css'

import msg from '@/msg'
import { expose } from '@/utils'

expose({ msg })

msg.init()
