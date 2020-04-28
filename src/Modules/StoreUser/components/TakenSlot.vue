<template>
  <b-dropdown
    v-b-tooltip="profile.name"
    no-caret
    toggle-class="btn p-0 filled"
  >
    <template v-slot:button-content>
      <Avatar
        :url="profile.avatar"
        :size="35"
        :class="{pending: !confirmed, confirmed: confirmed}"
      />
      <div :class="{'slotstatus': true, pending: !confirmed, confirmed: confirmed}">
        <i :class="{'slotstatus-icon fas': true, 'fa-clock': !confirmed, 'fa-check-circle': confirmed}" />
      </div>
    </template>
    <b-dropdown-item :href="`/profile/${profile.id}`">
      <i class="fas fa-user" /> {{ $i18n('pickup.open_profile') }}
    </b-dropdown-item>
    <b-dropdown-item
      v-if="allowChat"
      @click="openChat"
    >
      <i class="fas fa-comment" /> {{ $i18n('chat.open_chat') }}
    </b-dropdown-item>
    <b-dropdown-item
      v-if="!confirmed && allowConfirm"
      @click="$emit('confirm', profile.id)"
    >
      <i class="fas fa-check" /> {{ $i18n('pickup.confirm') }}
    </b-dropdown-item>
    <b-dropdown-item
      v-if="allowLeave"
      @click="$emit('leave')"
    >
      <i class="fa fa-times-circle" /> {{ $i18n('pickup.leave') }}
    </b-dropdown-item>
    <b-dropdown-item
      v-if="allowKick && !allowLeave"
      @click="$emit('kick', profile.id)"
    >
      <i class="fas fa-times-circle" /> {{ $i18n('pickup.kick') }}
    </b-dropdown-item>
  </b-dropdown>
</template>

<script>
import Avatar from '@/components/Avatar'
import { BDropdown, BDropdownItem } from 'bootstrap-vue'
import conv from '@/conv'

export default {
  components: { Avatar, BDropdown, BDropdownItem },
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
    position: absolute;
    top: 0px;
    right: 2px;
    height: 12px;
    width: 12px;
    transform: rotate(45deg);
    opacity: 0.8;
    background-color: var(--fs-beige);
    box-shadow: 0 0 2px 0px var(--fs-brown);
  }
  .slotstatus.pending {
    color: var(--danger);
  }
  .slotstatus.confirmed {
    color: var(--fs-green);
  }
  .avatar.pending {
    opacity: 0.33;
  }
  /* Check / Clock inside the statuspatch */
  .slotstatus-icon {
    position: absolute;
    display: inline-block;
    bottom: 1px;
    right: 1px;
    transform: rotate(-45deg);
    font-size: 10px;
  }
</style>
