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
      />
      <span :class="{slotstatus: true, pending: !confirmed, confirmed: confirmed}" />
    </template>
    <b-dropdown-item
      v-if="allowLeave"
      @click="$emit('leave')"
    >
      Austragen
    </b-dropdown-item>
    <b-dropdown-item
      v-if="allowKick && !allowLeave"
      @click="$emit('kick', profile.id)"
    >
      Austragen
    </b-dropdown-item>
    <b-dropdown-item :href="`/profile/${profile.id}`">
      Profil aufrufen
    </b-dropdown-item>
    <b-dropdown-item
      v-if="!confirmed && allowConfirm"
      @click="$emit('confirm', profile.id)"
    >
      Bestätigen
    </b-dropdown-item>
  </nav-item-dropdown>
</template>

<script>
import Avatar from '@/components/Avatar'
// import bDropdown from '@b/components/dropdown/dropdown'
// use custom navItemDropdown for now, better tooltip support and look...
import NavItemDropdown from '@/components/Topbar/NavItemDropdown'
import bDropdownItem from '@b/components/dropdown/dropdown-item'

export default {
  components: { Avatar, NavItemDropdown, bDropdownItem },
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
  .pending {
    background-image: url(/img/pending.png);
  }
  .confirmed {
    background-image: url(/img/check.png);
  }
</style>
