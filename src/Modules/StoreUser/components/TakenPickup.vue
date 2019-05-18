<template>
  <div>
    <b-dropdown
      v-b-tooltip
      :title="profile.name"
      size="sm"
      no-caret
      variant="primary"
    >
      <template slot="button-content">
        <Avatar :url="profile.avatar" />
      </template>
      <b-dropdown-item
        v-if="allowLeave"
        @click="$refs.modal_leave.show()"
      >
        Austragen
      </b-dropdown-item>
      <b-dropdown-item
        v-if="allowKick"
        @click="$refs.modal_kick.show()"
      >
        Austragen
      </b-dropdown-item>
      <b-dropdown-item :href="`/profile/${profile.id}`">
        Profil aufrufen
      </b-dropdown-item>
    </b-dropdown>
    <b-modal
      ref="modal_leave"
      v-if="allowLeave"
      :title="$i18n('pickup.really_leave')"
      :cancel-title="$i18n('button.abort')"
      :ok-title="$i18n('button.yes_i_am_sure')"
      @ok="$emit('leave')"
    >
      <p>{{ $i18n('pickup.really_leave_pickup_date', {'date': date}) }}</p>
    </b-modal>
    <b-modal
      ref="modal_kick"
      v-if="allowKick"
      :title="$i18n('pickup.really_kick')"
      :cancel-title="$i18n('button.abort')"
      :ok-title="$i18n('button.yes_i_am_sure')"
      @ok="$emit('kick')"
    >
      <p>{{ $i18n('pickup.really_kick_user_pickup_date', {'date': date, 'name': profile.name}) }}</p>
    </b-modal>
  </div>
</template>

<script>
import Avatar from '@/components/Avatar'
import bDropdown from '@b/components/dropdown/dropdown'
import bDropdownItem from '@b/components/dropdown/dropdown-item'
import bModal from '@b/components/modal/modal'
import bTooltip from '@b/directives/tooltip/tooltip'
 
export default {
  components: { Avatar, bDropdown, bDropdownItem, bModal },
  directives: { bTooltip },
  props: {
    date: {
      type: Date,
      default: null
    },
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
    }
  }
}
</script>

<style scoped>

</style>
