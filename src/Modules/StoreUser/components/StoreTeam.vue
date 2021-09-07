<template>
  <!--
  Default team list order, sorted by number of pickups each:
  - store managers
  - active team members
  - unverified team members
  - jumpers
  Sleeping team members will come last in each of those sections.
  Check `tableSortFunction` (and StoreGateway:getStoreTeam) for details.
  -->
  <div :class="['bootstrap store-team w-100', `team-${storeId}`]">
    <div class="card rounded mb-2">
      <div class="card-header text-white bg-primary">
        <div class="row align-items-center">
          <div class="col font-weight-bold">
            {{ $i18n('store.teamName', { storeTitle }) }}
          </div>

          <div class="col col-4 text-right">
            <button
              v-if="mayEditStore"
              v-b-tooltip.hover.top
              :title="$i18n(managementModeEnabled ? 'store.sm.managementToggleOff' : 'store.sm.managementToggleOn')"
              :class="[managementModeEnabled ? ['text-warning', 'active'] : 'text-light', 'btn', 'btn-secondary', 'btn-sm']"
              href="#"
              @click.prevent="toggleManageControls"
            >
              <i class="fas fa-fw fa-cog" />
            </button>
            <button
              class="px-1 d-md-none text-light btn btn-sm"
              href="#"
              @click.prevent="toggleTeamDisplay"
            >
              <i :class="['fas fa-fw', `fa-chevron-${displayMembers ? 'down' : 'left'}`]" />
            </button>
          </div>
        </div>
      </div>

      <!-- preparation for more store-management features -->
      <StoreManagementPanel
        v-if="managementModeEnabled"
        :store-id="storeId"
        :team="team"
        classes="p-2 team-management"
        :region-id="regionId"
      />

      <div class="card-body team-list">
        <b-table
          ref="teamlist"
          :items="foodsaver"
          :fields="tableFields"
          :class="{'d-none': !displayMembers}"
          details-td-class="col-actions"
          primary-key="id"
          thead-class="d-none"
          sort-by="ava"
          :busy="isBusy"
          :sort-desc.sync="sortdesc"
          :sort-compare="sortfun"
          show-empty
          sort-null-last
        >
          <template #cell(ava)="data">
            <StoreTeamAvatar :user="data.item" />
          </template>

          <template #cell(info)="data">
            <StoreTeamInfo
              :user="data.item"
              :store-manager-view="managementModeEnabled"
              @toggle-details="toggleActions(data)"
            />
          </template>

          <template #cell(mobinfo)="data">
            <StoreTeamInfotext
              :member="data.item"
              :may-edit-store="mayEditStore"
            />
          </template>

          <template #cell(call)="data">
            <b-button
              v-if="data.item.callable || !data.item.copyNumber"
              variant="link"
              class="member-call"
              :href="data.item.callable"
              :disabled="!data.item.callable"
            >
              <i class="fas fa-fw fa-phone" />
            </b-button>
            <b-button
              v-else-if="data.item.copyNumber && canCopy"
              variant="link"
              class="member-call copy-clipboard"
              href="#"
              @click.prevent="copyIntoClipboard(data.item.copyNumber)"
            >
              <i class="fas fa-fw fa-clone" />
            </b-button>
          </template>

          <template #row-details="data">
            <StoreTeamInfotext
              v-if="wXS"
              :member="data.item"
              :may-edit-store="mayEditStore"
              classes="text-center"
            />

            <div class="member-actions">
              <b-button
                v-if="(wXS || wSM)"
                size="sm"
                :href="`/profile/${data.item.id}`"
              >
                <i class="fas fa-fw fa-user" />
                {{ $i18n('pickup.open_profile') }}
              </b-button>

              <b-button
                v-if="data.item.id !== fsId"
                size="sm"
                variant="secondary"
                :block="!(wXS || wSM)"
                @click="openChat(data.item.id)"
              >
                <i class="fas fa-fw fa-comment" />
                {{ $i18n('chat.open_chat') }}
              </b-button>

              <b-button
                v-if="mayEditStore && data.item.isJumper"
                size="sm"
                variant="primary"
                :block="!(wXS || wSM)"
                @click="toggleStandbyState(data.item.id, false)"
              >
                <i class="fas fa-fw fa-clipboard-check" />
                {{ $i18n('store.sm.makeRegularTeamMember') }}
              </b-button>

              <b-button
                v-if="mayEditStore && data.item.isActive && !data.item.isManager"
                size="sm"
                variant="primary"
                :block="!(wXS || wSM)"
                @click="toggleStandbyState(data.item.id, true)"
              >
                <i class="fas fa-fw fa-mug-hot" />
                {{ $i18n('store.sm.makeJumper') }}
              </b-button>

              <b-button
                v-if="managementModeEnabled && mayBecomeManager(data.item)"
                size="sm"
                variant="warning"
                :block="!(wXS || wSM)"
                @click="promoteToManager(data.item.id)"
              >
                <i class="fas fa-fw fa-cog" />
                {{ $i18n('store.sm.promoteToManager') }}
              </b-button>

              <b-button
                v-if="managementModeEnabled && data.item.isManager"
                size="sm"
                variant="outline-primary"
                :block="!(wXS || wSM)"
                @click="demoteAsManager(data.item.id, data.item.name)"
              >
                <i class="fas fa-fw fa-cog" />
                {{ $i18n('store.sm.demoteAsManager') }}
              </b-button>

              <b-button
                v-if="mayRemoveFromStore(data.item)"
                size="sm"
                variant="danger"
                :block="!(wXS || wSM)"
                @click="removeFromTeam(data.item.id, data.item.name)"
              >
                <i class="fas fa-fw fa-user-times" />
                {{ $i18n('store.sm.removeFromTeam') }}
              </b-button>
            </div>
          </template>
        </b-table>
      </div>
    </div>
  </div>
</template>

<script>
import _ from 'underscore'
import fromUnixTime from 'date-fns/fromUnixTime'
import compareAsc from 'date-fns/compareAsc'

import {
  demoteAsStoreManager, promoteToStoreManager,
  moveMemberToStandbyTeam, moveMemberToRegularTeam,
  removeStoreMember,
} from '@/api/stores'
import i18n from '@/i18n'
import { callableNumber } from '@/utils'
import { chat, pulseSuccess, pulseError } from '@/script'
import MediaQueryMixin from '@/utils/VueMediaQueryMixin'

import { legacyXhrCall } from './legacy'
import StoreManagementPanel from './StoreManagementPanel'
import StoreTeamAvatar from './StoreTeamAvatar'
import StoreTeamInfo from './StoreTeamInfo'
import StoreTeamInfotext from './StoreTeamInfotext'

export default {
  components: { StoreManagementPanel, StoreTeamAvatar, StoreTeamInfo, StoreTeamInfotext },
  mixins: [MediaQueryMixin],
  props: {
    fsId: { type: Number, required: true },
    mayEditStore: { type: Boolean, default: false },
    team: { type: Array, required: true },
    storeId: { type: Number, required: true },
    storeTitle: { type: String, default: '' },
    regionId: { type: Number, required: true },
  },
  data () {
    return {
      foodsaver: _.map(this.team, this.foodsaverData),
      sortfun: this.tableSortFunction,
      sortdesc: true,
      managementModeEnabled: false,
      displayMembers: true,
      isBusy: false,
    }
  },
  computed: {
    tableFields () {
      const fields = [
        { key: 'ava', class: 'col-ava', sortable: true },
        { key: 'info', class: 'col-info' },
      ]
      if (this.wSM) {
        fields.push({ key: 'mobinfo', class: 'col-mobinfo' })
      }
      if (this.wXS || this.wSM) {
        fields.push({ key: 'call', class: 'col-call' })
      }
      return fields
    },
  },
  methods: {
    toggleManageControls () {
      this.sortfun = this.managementModeEnabled ? this.tableSortFunction : this.pickupSortFunction
      this.managementModeEnabled = !this.managementModeEnabled
    },
    toggleTeamDisplay () {
      this.displayMembers = !this.displayMembers
    },
    canCopy () {
      return !!navigator.clipboard
    },
    copyIntoClipboard (text) {
      if (navigator.clipboard) {
        navigator.clipboard.writeText(text).then(() => {
          pulseSuccess(i18n('pickup.copiedNumber', { number: text }))
        })
      }
    },
    mayRemoveFromStore (user) {
      if (user.isManager) return false
      if (user.id === this.fsId) return true
      return this.mayEditStore
    },
    mayBecomeManager (user) {
      if (!user.mayManage) return false
      if (user.isJumper) return false
      return !user.isManager
    },
    toggleActions (row) {
      const wasOpen = row.detailsShowing
      this.foodsaver.forEach((item) => {
        if (item._showDetails) {
          // Firefox has some funny ideas about focus handling, so we must all suffer
          this.$root.$emit('bv::hide::tooltip', 'member-' + item.id)
          // close previously open action list
          this.$set(item, '_showDetails', false)
        }
      })
      if (!wasOpen) {
        row.toggleDetails()
      }
    },
    openChat (fsId) {
      chat(fsId)
    },
    async toggleStandbyState (fsId, newStatusIsStandby) {
      this.isBusy = true
      try {
        if (newStatusIsStandby) {
          await moveMemberToStandbyTeam(this.storeId, fsId)
        } else {
          await moveMemberToRegularTeam(this.storeId, fsId)
        }
      } catch (e) {
        pulseError(i18n('error_unexpected'))
        this.isBusy = false
        return
      }
      const index = this.team.findIndex(fs => fs.id === fsId)
      if (index >= 0) {
        const fs = this.foodsaver[index]
        fs.isWaiting = newStatusIsStandby
        fs.isJumper = newStatusIsStandby
        fs.isActive = !newStatusIsStandby
        fs._showDetails = false
        this.$set(this.team, index, fs)
      }
      this.isBusy = false
    },
    async removeFromTeam (fsId, fsName) {
      if (!fsId) {
        return
      }
      if (!confirm(i18n('store.sm.reallyRemove', { name: fsName }))) {
        return
      }

      this.isBusy = true
      try {
        await removeStoreMember(this.storeId, fsId)
      } catch (e) {
        pulseError(i18n('error_unexpected'))
        this.isBusy = false
        return
      }
      const index = this.foodsaver.findIndex(member => member.id === fsId)
      if (index >= 0) {
        this.foodsaver.splice(index, 1)
      }
      this.isBusy = false
    },
    async promoteToManager (fsId) {
      if (!fsId) {
        return
      }
      this.isBusy = true
      try {
        await promoteToStoreManager(this.storeId, fsId)
      } catch (e) {
        pulseError(i18n('error_unexpected'))
        this.isBusy = false
        return
      }
      const index = this.team.findIndex(fs => fs.id === fsId)
      if (index >= 0) {
        const fs = this.foodsaver[index]
        fs.isManager = true
        fs._rowVariant = 'warning'
        fs._showDetails = false
        this.$set(this.team, index, fs)
      }
      this.isBusy = false
    },
    async demoteAsManager (fsId, fsName) {
      if (!fsId) {
        return
      }
      if (!confirm(i18n('store.sm.reallyDemote', { name: fsName }))) {
        return
      }
      this.isBusy = true
      try {
        await demoteAsStoreManager(this.storeId, fsId)
      } catch (e) {
        pulseError(i18n('error_unexpected'))
        this.isBusy = false
        return
      }
      const index = this.team.findIndex(fs => fs.id === fsId)
      if (index >= 0) {
        const fs = this.foodsaver[index]
        fs.isManager = false
        fs._rowVariant = ''
        fs._showDetails = false
        this.$set(this.team, index, fs)
      }
      this.isBusy = false
    },
    /* eslint-disable brace-style */
    pickupSortFunction (a, b, key, directionDesc) {
      const direction = directionDesc ? 1 : -1
      // ORDER BY
      // isManager (verantwortlich) DESC
      if (a.isManager !== b.isManager) { return direction * (a.isManager - b.isManager) }
      // lastPickup (last_fetch) DESC
      if (a.lastPickup && b.lastPickup) { return direction * compareAsc(a.lastPickup, b.lastPickup) }
      else if (a.lastPickup) { return direction }
      else if (b.lastPickup) { return -1 * direction }
      // joinDate (add_date) DESC
      if (a.joinDate && b.joinDate) { return direction * compareAsc(a.joinDate, b.joinDate) }
      else if (a.joinDate) { return direction }
      else if (b.joinDate) { return -1 * direction }
      // name ASC
      return -1 * direction * a.name.localeCompare(b.name)
    },
    /* eslint-enable brace-style */
    tableSortFunction (a, b, key, directionDesc) {
      const direction = directionDesc ? 1 : -1
      // ORDER BY
      // isManager (verantwortlich) DESC
      if (a.isManager !== b.isManager) { return direction * (a.isManager - b.isManager) }
      // isJumper (team_active == MembershipStatus::JUMPER) ASC
      if (a.isJumper !== b.isJumper) { return -1 * direction * (a.isJumper - b.isJumper) }
      // isVerified (verified == 1) DESC
      if (a.isVerified !== b.isVerified) { return direction * (a.isVerified - b.isVerified) }
      // sleepStatus (sleep_status) ASC
      if (a.sleepStatus !== b.sleepStatus) { return -1 * direction * (a.sleepStatus - b.sleepStatus) }
      // fetchCount (stat_fetchcount) DESC
      if (a.fetchCount !== b.fetchCount) { return direction * (a.fetchCount - b.fetchCount) }
      // lastPickup (last_fetch) DESC
      if (a.lastPickup && b.lastPickup) { return direction * compareAsc(a.lastPickup, b.lastPickup) }
      // joinDate (add_date) DESC
      if (a.joinDate && b.joinDate) { return direction * compareAsc(a.joinDate, b.joinDate) }
      // name ASC
      return -1 * direction * a.name.localeCompare(b.name)
    },
    foodsaverData (fs) {
      if (!fs) {
        return {}
      }

      return {
        id: fs.id,
        isActive: fs.team_active === 1, // MembershipStatus::MEMBER
        isJumper: fs.team_active === 2, // MembershipStatus::JUMPER
        isManager: !!fs.verantwortlich,
        _rowVariant: fs.verantwortlich ? 'warning' : '',
        isVerified: fs.verified === 1,
        mayManage: fs.quiz_rolle >= 2, // Role::STORE_MANAGER
        // mayAmb: fs.quiz_rolle >= 3, // Role::AMBASSADOR
        avatar: fs.photo,
        isWaiting: fs.team_active === 2 || fs.verified < 1, // MembershipStatus::JUMPER or unverified
        sleepStatus: fs.sleep_status,
        name: fs.name,
        number: fs.handy || fs.telefon || '',
        callable: callableNumber(fs.handy) || callableNumber(fs.telefon) || '',
        copyNumber: callableNumber(fs.handy, true) || callableNumber(fs.telefon, true) || '',
        phone: fs.telefon,
        joinDate: fs.add_date ? fromUnixTime(fs.add_date) : null,
        lastPickup: fs.last_fetch ? fromUnixTime(fs.last_fetch) : null,
        fetchCount: fs.stat_fetchcount,
      }
    },
    legacyXhrCall,
  },
}
</script>

<style lang="scss" scoped>
.store-team .team-management {
  border-bottom: 2px solid var(--warning);
}

.store-team .team-list {
  padding: 0;
}

.store-team ::v-deep table {
  display: flex;
  flex-direction: row;
  margin-bottom: 0;

  thead, tbody {
    width: 100%;

    tr {
      display: flex;
      border-bottom: 1px solid var(--border);

      &.b-table-details {
        justify-content: center;
      }

      &.table-warning {
        border-bottom-width: 2px;
        border-bottom-color: var(--warning);
        padding-bottom: 1px;
      }

      &:last-child,
      &.b-table-has-details {
        border-bottom-width: 0;
      }

      td {
        border-top: 0;
      }
    }
  }

  tr td {
    padding: 3px;
    border-top-color: var(--border);
    vertical-align: middle;
    cursor: default;
    display: inline-block;

    &.col-actions {
      padding: 0;
    }

    &.col-ava {
      position: relative;
      align-self: center;
    }

    &.col-info {
      flex-grow: 1;
    }

    &.col-mobinfo {
      padding: 0 10px;
      text-align: right;
    }

    &.col-call {
      .member-call {
        padding: 10px;
        align-self: center;
        color: var(--fs-green);

        &.copy-clipboard { opacity: 0.7; }

        &:hover {
          background-color: var(--fs-green);
          color: var(--white);
        }
        &:focus {
          outline: 2px solid var(--fs-green);
        }
        &:disabled {
          color: var(--fs-beige);
        }
      }
    }

    .member-actions {
      padding: 5px 0;

      .btn {
        margin-bottom: 5px;
      }
    }
  }
}
</style>
