<template>
  <li :class="confirmed ? 'confirmed' : 'pending'">
    <b-dropdown
      :tooltip="profile.name"
      extra-toggle-classes="p-0"
      size="sm"
      no-caret
      variant="primary"
    >
      <template slot="button-content">
        <Avatar :url="profile.avatar" />
        <span class="slotstatus" />
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
    </b-dropdown>
  </li>
</template>

<script>
import Avatar from '@/components/Avatar'
// import bDropdown from '@b/components/dropdown/dropdown'
// use custom navItemDropdown for now, better tooltip support and look...
import bDropdown from '@/components/Topbar/NavItemDropdown'
import bDropdownItem from '@b/components/dropdown/dropdown-item'

export default {
  components: { Avatar, bDropdown, bDropdownItem },
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
  .pending div {
    opacity: 0.4;
    filter: alpha(opacity=40);
  }
  .slotstatus {
    display: block;
    position: relative;
    top: -16px;
    right: -21px;
    width: 16px;
    height: 16px;
  }
  .pending .slotstatus {
    background-image: url(/img/pending.png);
  }
  .confirmed .slotstatus {
    background-image: url(/img/check.png);
  }
  .dropdown {
    box-sizing: border-box;
    width: 35px;
    height: 35px;
    border: none;
  }
</style>
