<template>
  <nav-item-dropdown
    :tooltip="profile.name"
    extra-toggle-classes="btn p-0 filled"
    size="sm"
    no-caret
    variant="primary"
  >
    <template slot="button-content">
      <Avatar
        :url="profile.avatar"
        :size="35"
        :class="{pending: !confirmed, confirmed:confirmed}"
      />
      <i :class="{slotstatus: true, 'far fa-clock': !confirmed, 'fas fa-check': confirmed}" />
    </template>
    <b-dropdown-item :href="`/profile/${profile.id}`">
      <i class="fa fa-user mr-1" /> {{ $i18n('pickup.open_profile') }}
    </b-dropdown-item>
    <b-dropdown-item
      v-if="allowChat"
      @click="openChat"
    >
      <i class="fa fa-comment mr-1" /> {{ $i18n('chat.open_chat') }}
    </b-dropdown-item>
    <b-dropdown-item
      v-if="!confirmed && allowConfirm"
      @click="$emit('confirm', profile.id)"
    >
      <i class="fa fa-check mr-1" /> {{ $i18n('pickup.confirm') }}
    </b-dropdown-item>
    <b-dropdown-item
      v-if="allowLeave"
      @click="$emit('leave')"
    >
      <i class="fa fa-times-circle mr-1" /> {{ $i18n('pickup.leave') }}
    </b-dropdown-item>
    <b-dropdown-item
      v-if="allowKick && !allowLeave"
      @click="$emit('kick', profile.id)"
    >
      <i class="fa fa-times-circle mr-1" /> {{ $i18n('pickup.kick') }}
    </b-dropdown-item>
  </nav-item-dropdown>
</template>

<script>
import Avatar from '@/components/Avatar'
// import bDropdown from '@b/components/dropdown/dropdown'
// use custom navItemDropdown for now, better tooltip support and look...
import NavItemDropdown from '@/components/Topbar/NavItemDropdown'
import { BDropdownItem } from 'bootstrap-vue'
import conv from '@/conv'

export default {
  components: { Avatar, NavItemDropdown, BDropdownItem },
  props: {
    profile: {
      type: Object,
      default: null
    },
    confirmed: {
      type: Boolean,
      default: false
    },
    allowLeave: {
      type: Boolean,
      default: false
    },
    allowKick: {
      type: Boolean,
      default: false
    },
    allowConfirm: {
      type: Boolean,
      default: false
    },
    allowChat: {
      type: Boolean,
      default: false
    }
  },
  methods: {
    openChat () {
      conv.userChat(this.profile.id)
    }
  }
}
</script>

<style scoped>
  .slotstatus {
    display: block;
    position: absolute;
    bottom: 0;
    right: 0;
    width: 16px;
    height: 16px;
  }

  .fa {
    margin-left: -5px;
  }

/* For slotstatus pending */
  .fa-clock {
    color: var(--danger);
  }

  .pending {
    opacity: 0.5;
  }

/* For slotstatus confirmed */
  .fa-check {
    color: var(--green);
  }
</style>
