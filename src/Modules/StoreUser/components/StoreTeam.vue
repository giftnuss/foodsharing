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
  <div :class="['bootstrap store-team', `team-${storeId}`]">
    <div class="head ui-widget-header ui-corner-top d-flex justify-content-between">
      <span>{{ $i18n('store.teamName', { storeTitle }) }}</span>

      <div class="align-self-center">
        <a
          v-if="mayEditStore"
          v-b-tooltip.hover.top="$i18n(managementModeEnabled ? 'store.sm.managementToggleOff' : 'store.sm.managementToggleOn')"
          :class="['px-1', managementModeEnabled ? 'text-warning' : 'text-light']"
          href="#"
          @click.prevent="toggleManageControls"
        >
          <i class="fas fa-fw fa-cog" />
        </a>
        <a
          class="px-1 d-md-none text-light"
          href="#"
          @click.prevent="toggleTeamDisplay"
        >
          <i :class="['fas fa-fw', `fa-chevron-${displayMembers ? 'down' : 'left'}`]" />
        </a>
      </div>
    </div>

    <div
      v-if="managementModeEnabled"
      class="bg-white ui-corner-top p-2 team-management"
    >
      <span class="text-muted">{{ $i18n('store.sm.managementEffect') }}</span>
    </div>

    <!-- preparation for more store-management features -->
    <StoreManagementPanel
      v-if="false && managementModeEnabled"
      classes="p-2 team-management"
    />

    <div class="corner-bottom margin-bottom team-list">
      <b-table
        ref="teamlist"
        :items="foodsaver"
        :fields="tableFields"
        :class="{'d-none': !displayMembers}"
        details-td-class="col-actions"
        primary-key="id"
        thead-class="d-none"
        sort-by="ava"
        :sort-desc.sync="sortdesc"
        :sort-compare="sortfun"
        show-empty
        sort-null-last
      >
        <template v-slot:cell(ava)="data">
          <StoreTeamAvatar :user="data.item" />
        </template>

        <template v-slot:cell(info)="data">
          <StoreTeamInfo
            :user="data.item"
            :store-manager-view="managementModeEnabled"
            @toggle-details="toggleActions(data)"
          />
        </template>

        <template v-slot:cell(mobinfo)="data">
          <StoreTeamInfotext
            :member="data.item"
            :may-edit-store="mayEditStore"
          />
        </template>

        <template v-slot:cell(call)="data">
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

        <template v-slot:row-details="data">
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
              @click="changeMembershipStatus(data.item.id, 'toteam')"
            >
              <i class="fas fa-fw fa-clipboard-check" />
              {{ $i18n('store.sm.makeRegularTeamMember') }}
            </b-button>

            <b-button
              v-if="mayEditStore && data.item.isActive && !data.item.isManager"
              size="sm"
              variant="primary"
              :block="!(wXS || wSM)"
              @click="changeMembershipStatus(data.item.id, 'tojumper')"
            >
              <i class="fas fa-fw fa-mug-hot" />
              {{ $i18n('store.sm.makeJumper') }}
            </b-button>

            <b-button
              v-if="mayEditStore && !data.item.isManager"
              size="sm"
              variant="danger"
              :block="!(wXS || wSM)"
              @click="removeFromTeam(data.item)"
            >
              <i class="fas fa-fw fa-user-times" />
              {{ $i18n('store.sm.removeFromTeam') }}
            </b-button>
          </div>
        </template>
      </b-table>
    </div>
  </div>
</template>

<script>
import _ from 'underscore'
import fromUnixTime from 'date-fns/fromUnixTime'
import compareAsc from 'date-fns/compareAsc'

import i18n from '@/i18n'
import { callableNumber } from '@/utils'
import { xhrf, chat, pulseSuccess } from '@/script'
import MediaQueryMixin from '@/utils/VueMediaQueryMixin'

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
  },
  data () {
    return {
      sortfun: this.tableSortFunction,
      sortdesc: true,
      managementModeEnabled: false,
      displayMembers: true,
    }
  },
  computed: {
    foodsaver () {
      return _.map(this.team, this.foodsaverData)
    },
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
    // FIXME convert this XHR-reliant method to REST + StoreTransactions after !1475 is merged
    changeMembershipStatus (fsId, newStatus) {
      const fData = {
        bid: this.storeId,
        fsid: fsId,
        action: newStatus,
      }
      xhrf('bcontext', fData)
      const index = this.team.findIndex(fs => fs.id === fsId)
      if (index >= 0) {
        const fs = this.foodsaver[index]
        fs.isJumper = (newStatus === 'tojumper')
        fs.isActive = !fs.isJumper
        fs._showDetails = false
        this.$set(this.team, index, fs)
      }
    },
    // FIXME convert this XHR-reliant method to REST + StoreTransactions after !1475 is merged
    removeFromTeam (fs) {
      if (!fs || !fs.id) {
        return
      }
      if (!confirm(i18n('store.sm.reallyRemove', { name: fs.name }))) {
        return
      }
      const fsId = fs.id
      const fData = {
        bid: this.storeId,
        fsid: fsId,
        action: 'delete',
      }
      xhrf('bcontext', fData)
      const index = this.foodsaver.findIndex(member => member.id === fsId)
      if (index >= 0) {
        this.foodsaver.splice(index, 1)
        this.$refs.teamlist.refresh()
      }
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
  },
}
</script>

<style lang="scss" scoped>
.store-team .team-management {
  border-bottom: 2px solid var(--warning);
}

.store-team .team-list {
  background: var(--white);
}

.store-team ::v-deep table {
  display: flex;
  flex-direction: row;

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
