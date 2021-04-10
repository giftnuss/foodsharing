<template>
  <b-dropdown
    :id="`slot-${uniqueId}`"
    no-caret
    toggle-class="btn p-0 filled"
  >
    <b-tooltip
      :target="`slot-${uniqueId}`"
      triggers="hover blur"
    >
      <div>
        {{ profile.name }}
      </div>
      <div v-if="!confirmed">
        ({{ $i18n('pickup.to_be_confirmed') }})
      </div>
    </b-tooltip>
    <template #button-content>
      <Avatar
        :url="profile.avatar"
        :size="50"
        :class="{'pending': !confirmed, 'confirmed': confirmed}"
      />
      <div :class="{'slotstatus': true, 'pending': !confirmed, 'confirmed': confirmed}">
        <i :class="{'slotstatus-icon fas': true, 'fa-clock': !confirmed, 'fa-check-circle': confirmed}" />
      </div>
    </template>
    <b-dropdown-item :href="`/profile/${profile.id}`">
      <i class="fas fa-fw fa-user" /> {{ $i18n('pickup.open_profile') }}
    </b-dropdown-item>
    <b-dropdown-item
      v-if="allowChat && !isMe"
      @click="openChat"
    >
      <i class="fas fa-fw fa-comment" /> {{ $i18n('chat.open_chat') }}
    </b-dropdown-item>
    <b-dropdown-item
      v-if="callLink && !isMe"
      :href="callLink"
    >
      <i class="fas fa-fw fa-phone" /> {{ $i18n('pickup.call') }}
    </b-dropdown-item>
    <b-dropdown-item
      v-else-if="callText && !isMe"
      @click="copyIntoClipboard(callText)"
    >
      <!-- eslint-disable-next-line vue/max-attributes-per-line -->
      <i class="fas fa-fw" :class="[canCopy ? 'fa-clone' : 'fa-phone-slash']" />
      <span v-if="canCopy">{{ $i18n('pickup.copyNumber') }}</span>
      <span v-else>{{ callText }}</span>
    </b-dropdown-item>
    <b-dropdown-item
      v-if="!confirmed && allowConfirm"
      @click="$emit('confirm', profile.id)"
    >
      <i class="fas fa-fw fa-check" /> {{ $i18n('pickup.confirm') }}
    </b-dropdown-item>
    <b-dropdown-item
      v-if="allowLeave"
      @click="$emit('leave')"
    >
      <i class="fa fa-fw fa-times-circle" /> {{ $i18n('pickup.leave') }}
    </b-dropdown-item>
    <b-dropdown-item
      v-if="allowKick && !allowLeave"
      @click="$emit('kick', profile.id)"
    >
      <i class="fas fa-fw fa-times-circle" /> {{ $i18n('pickup.kick') }}
    </b-dropdown-item>
  </b-dropdown>
</template>

<script>
import Avatar from '@/components/Avatar'
import { BDropdown, BDropdownItem } from 'bootstrap-vue'
import { pulseSuccess } from '@/script'
import { callableNumber } from '@/utils'
import conv from '@/conv'
import i18n from '@/i18n'
import serverData from '@/server-data'
import { v4 as uuidv4 } from 'uuid'

export default {
  components: { Avatar, BDropdown, BDropdownItem },
  props: {
    profile: {
      type: Object,
      default: null,
    },
    confirmed: {
      type: Boolean,
      default: false,
    },
    allowLeave: {
      type: Boolean,
      default: false,
    },
    allowKick: {
      type: Boolean,
      default: false,
    },
    allowConfirm: {
      type: Boolean,
      default: false,
    },
    allowChat: {
      type: Boolean,
      default: false,
    },
  },
  data () {
    return {
      uniqueId: null,
    }
  },
  computed: {
    callLink () {
      const number = callableNumber(this.profile.mobile) || callableNumber(this.profile.landline)
      return number || ''
    },
    callText () {
      const number = callableNumber(this.profile.mobile, true) || callableNumber(this.profile.landline, true)
      return number || ''
    },
    canCopy () {
      return !!navigator.clipboard
    },
    isMe () {
      return serverData.user.id === this.profile.id
    },
  },
  mounted () {
    this.uniqueId = uuidv4()
  },
  methods: {
    copyIntoClipboard (text) {
      if (navigator.clipboard) {
        navigator.clipboard.writeText(text).then(() => {
          pulseSuccess(i18n('pickup.copiedNumber', { number: text }))
        })
      }
    },
    openChat () {
      conv.userChat(this.profile.id)
    },
  },
}
</script>

<style lang="scss" scoped>
.slotstatus {
  position: absolute;
  top: -2px;
  right: 0;
  height: 16px;
  width: 16px;
  z-index: 3;
  transform: rotate(45deg);
  opacity: 0.9;
  background-color: var(--fs-beige);
  box-shadow: 0 0 3px 0px var(--fs-brown);

  &.pending {
    color: var(--danger);
  }
  &.confirmed {
    color: var(--fs-green);
  }

  // Check / Clock inside the statuspatch
  .slotstatus-icon {
    position: absolute;
    display: inline-block;
    bottom: 1px;
    right: 1px;
    transform: rotate(-45deg);
    font-size: 14px;
  }
}

.avatar.pending {
  opacity: 0.33;
}
</style>
