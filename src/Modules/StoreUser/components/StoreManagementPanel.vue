<template>
  <div
    class="bg-white ui-corner-top"
    :class="classes"
  >
    <span class="text-muted">{{ $i18n('store.sm.makeRegularTeamMember') }}</span>

    <b-input-group class="m-1 add-to-team">
      <b-form-input
        id="new-foodsaver-id"
        v-model="newUserId"
        class="with-border"
        :placeholder="$i18n('profile.infos.foodsaverId')"
      />
      <b-input-group-append>
        <b-button
          v-b-tooltip="$i18n('store.sm.makeRegularTeamMember')"
          :disabled="!newUserId.trim()"
          variant="secondary"
          type="submit"
          size="sm"
          @click.prevent="addNewTeamMember"
        >
          <i class="fas fa-fw fa-user-plus" />
        </b-button>
      </b-input-group-append>
    </b-input-group>

    <div v-if="requireReload">
      <span class="text-muted d-inline-block py-2">{{ $i18n('store.sm.reloadRequired') }}</span>
      <b-button
        variant="secondary"
        block
        class="reload-page"
        @click.prevent="reload"
      >
        <i class="fas fa-fw fa-sync-alt" />
        {{ $i18n('store.sm.reloadPage') }}
      </b-button>
    </div>

    <hr>

    <span class="text-muted">{{ $i18n('store.sm.managementEffect') }}</span>

    <b-button-toolbar
      class="flex-md-column p-1 d-none"
      key-nav
      justify
      :aria-label="$i18n('store.sm.managementActions')"
    >
      <b-button-group
        v-b-tooltip.hover.top="$i18n('store.sm.byLastPickup')"
        size="sm"
        class="m-1 manage-sort"
      >
        <b-button
          variant="secondary"
          disabled
          class="last-pickup"
        >
          <i class="fas fa-fw fa-user-clock" />
        </b-button>
        <b-button
          v-b-tooltip.hover.bottom="$i18n('store.sm.pickupDesc')"
          variant="light"
          class="last-pickup descending"
        >
          <i class="fas fa-fw fa-chevron-down" />
        </b-button>
        <b-button
          v-b-tooltip.hover.bottom="$i18n('store.sm.pickupAsc')"
          variant="light"
          class="last-pickup ascending"
        >
          <i class="fas fa-fw fa-chevron-up" />
        </b-button>
        <b-button
          v-b-tooltip.hover.bottom="$i18n('store.sm.pickupReset')"
          variant="light"
          class="last-pickup reset"
        >
          <i class="fas fa-fw fa-sort" />
        </b-button>
      </b-button-group>

      <b-button-group
        v-b-tooltip.hover.top="$i18n('store.sm.filter')"
        size="sm"
        class="m-1 manage-filter"
      >
        <b-button
          variant="warning"
          disabled
          class="filter"
        >
          <i class="fas fa-fw fa-filter" />
        </b-button>
        <b-button
          v-b-tooltip.hover.bottom="$i18n('store.sm.filterJumper')"
          variant="light"
          class="filter-jumper"
        >
          <i class="fas fa-fw fa-star" />
        </b-button>
        <b-button
          v-b-tooltip.hover.bottom="$i18n('store.sm.filterUnverified')"
          variant="light"
          class="filter-unverified"
        >
          <i class="fas fa-fw fa-eye-slash" />
        </b-button>
        <b-button
          v-b-tooltip.hover.bottom="$i18n('store.sm.filterQuizSM')"
          variant="light"
          class="filter-storemanager-quiz"
        >
          <i class="fas fa-fw fa-store-alt" />
        </b-button>
      </b-button-group>
    </b-button-toolbar>
  </div>
</template>

<script>
import { addStoreMember } from '@/api/stores'
import { reload } from '@/script'

export default {
  props: {
    classes: { type: String, default: '' },
    storeId: { type: Number, required: true },
  },
  data () {
    return {
      newUserId: '',
      // active sorting controls
      // active filtering controls
      requireReload: false,
    }
  },
  methods: {
    reload,
    async addNewTeamMember () {
      const userId = parseInt(this.newUserId)
      try {
        await addStoreMember(this.storeId, userId)
        this.newUserId = ''
      } catch (e) {
        console.error(e)
        // pulseError(i18n('error_unexpected'))
      }
      // convince user to trigger page reload for server refresh of teamlist
      this.requireReload = true
    },
  },
}
</script>

<style lang="scss" scoped>
</style>
