<template>
  <!--
  Default team list order, sorted by number of pickups each:
  - store managers
  - active team members
  - unverified team members
  - jumpers
  Sleeping team members will come last in each of those sections.
  Check StoreGateway:getStoreTeam for details.
  -->
  <div :class="['store-team', `team-${storeId}`]">
    <div class="head ui-widget-header ui-corner-top">
      {{ $i18n('store.teamName', { storeTitle }) }}
      <a
        class="float-right pl-2 pr-1 d-md-none text-light"
        href="#"
        @click.prevent="toggleTeamDisplay"
      >
        <i :class="['fas fa-fw', `fa-chevron-${displayMembers ? 'down' : 'left'}`]" />
      </a>
    </div>
    <div class="corner-bottom margin-bottom bootstrap team-list">
      <b-table
        ref="teamlist"
        :items="foodsaver"
        :fields="tableFields"
        :class="{'d-none': !displayMembers}"
        details-td-class="col-actions"
        primary-key="id"
        thead-class="d-none"
      >
        <template v-slot:cell(ava)="data">
          <a
            v-b-tooltip.hover="$i18n('pickup.open_profile')"
            :href="`/profile/${data.item.id}`"
          >
            <Avatar
              :url="data.item.avatar"
              :size="50"
              class="member-pic"
              :class="{'jumper': data.item.isJumper}"
              :sleep-status="data.item.sleepStatus"
            />
          </a>

          <!-- eslint-disable-next-line vue/max-attributes-per-line -->
          <b-tooltip :target="`fetchcount-${data.item.id}`" triggers="hover blur">
            <div>
              {{ $i18n('store.fetchCount', {'count': data.item.fetchCount}) }}
            </div>
            <div v-if="data.item.mayAmb">
              {{ $i18n('store.mayAmb') }}
            </div>
            <div v-if="data.item.mayManage">
              {{ $i18n('store.mayManage') }}
            </div>
            <div v-if="data.item.isJumper">
              {{ $i18n('store.isJumper') }}
            </div>
            <div v-if="!data.item.isVerified">
              {{ $i18n('store.isNotVerified') }}
            </div>
          </b-tooltip>
          <b-badge
            :id="`fetchcount-${data.item.id}`"
            class="member-fetchcount"
            :class="{'maysm': data.item.mayManage, 'waiting': data.item.isWaiting}"
            tag="span"
          >
            <span v-if="data.item.isJumper">
              <i class="fas fa-fw fa-star member-jumper" />
            </span>
            <span v-else-if="!data.item.isVerified">
              <i class="fas fa-fw fa-eye-slash member-unverified" />
            </span>
            <span v-else>{{ data.item.fetchCount }}</span>
          </b-badge>
        </template>

        <template v-slot:cell(info)="data">
          <!-- eslint-disable-next-line vue/max-attributes-per-line -->
          <b-tooltip :target="`member-${data.item.id}`" triggers="hover blur">
            <div v-if="data.item.isManager">
              {{ $i18n('store.isManager') }}
            </div>
            <div v-if="data.item.joinDate">
              {{ $i18n('store.memberSince', { date: $dateFormat(data.item.joinDate, 'day') }) }}
            </div>
            <div v-if="data.item.fetchCount && data.item.lastPickup">
              {{ $i18n('store.lastPickup', { date: $dateFormat(data.item.lastPickup, 'day') }) }}
            </div>
            <div v-else-if="!data.item.fetchCount">
              {{ $i18n('store.noPickup') }}
            </div>
            <div v-else-if="data.item.isJumper">
              {{ $i18n('store.isJumper') }}
            </div>
            <div v-else-if="!data.item.isVerified">
              {{ $i18n('store.isNotVerified') }}
            </div>
          </b-tooltip>
          <a
            :id="`member-${data.item.id}`"
            href="#memberdetails"
            class="member-info"
            :class="{'jumper': data.item.isJumper}"
            @click.prevent="toggleActions(data)"
          >
            <span class="member-name">
              {{ data.item.name }}
            </span>
            <span class="member-phone">
              {{ data.item.number }}
            </span>
            <span
              v-if="data.item.phone && (data.item.phone !== data.item.number)"
              class="member-phone"
            >
              {{ data.item.phone }}
            </span>
            <span
              v-if="data.item.fetchCount && data.item.lastPickup"
              class="text-muted"
            >
              {{ $i18n('store.lastPickupShort', { date: $dateDistanceInWords(data.item.lastPickup) }) }}
            </span>
          </a>
        </template>

        <template v-slot:cell(mobinfo)="data">
          <StoreTeamInfotext
            :member="data.item"
            :may-edit-store="mayEditStore"
          />
        </template>

        <template v-slot:cell(call)="data">
          <b-button
            variant="link"
            class="member-call"
            :href="data.item.callable"
            :disabled="!data.item.callable"
          >
            <i class="fas fa-fw fa-phone" />
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
              {{ $i18n('store.makeRegularTeamMember') }}
            </b-button>

            <b-button
              v-if="mayEditStore && data.item.isActive && !data.item.isManager"
              size="sm"
              variant="primary"
              :block="!(wXS || wSM)"
              @click="changeMembershipStatus(data.item.id, 'tojumper')"
            >
              <i class="fas fa-fw fa-mug-hot" />
              {{ $i18n('store.makeJumper') }}
            </b-button>

            <b-button
              v-if="mayEditStore && !data.item.isManager"
              size="sm"
              variant="danger"
              :block="!(wXS || wSM)"
              @click="removeFromTeam(data.item)"
            >
              <i class="fas fa-fw fa-user-times" />
              {{ $i18n('store.removeFromTeam') }}
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

import i18n from '@/i18n'
import { callableNumber } from '@/utils'
import { xhrf, chat } from '@/script'
import Avatar from '@/components/Avatar'
import MediaQueryMixin from '@/utils/VueMediaQueryMixin'

import StoreTeamInfotext from './StoreTeamInfotext'

export default {
  components: { Avatar, StoreTeamInfotext },
  mixins: [MediaQueryMixin],
  props: {
    fsId: { type: Number, required: true },
    mayEditStore: { type: Boolean, default: false },
    team: { type: Array, required: true },
    storeId: { type: Number, required: true },
    storeTitle: { type: String, default: '' }
  },
  data () {
    return {
      displayMembers: true
    }
  },
  computed: {
    foodsaver () {
      return _.map(this.team, this.foodsaverData)
    },
    tableFields () {
      const fields = [
        { key: 'ava', class: 'col-ava' },
        { key: 'info', class: 'col-info' }
      ]
      if (this.wSM) {
        fields.push({ key: 'mobinfo', class: 'col-mobinfo' })
      }
      if (this.wXS || this.wSM) {
        fields.push({ key: 'call', class: 'col-call' })
      }
      return fields
    }
  },
  methods: {
    toggleTeamDisplay () {
      this.displayMembers = !this.displayMembers
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
        action: newStatus
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
      if (!confirm(i18n('store.reallyRemove', { name: fs.name }))) {
        return
      }
      const fsId = fs.id
      const fData = {
        bid: this.storeId,
        fsid: fsId,
        action: 'delete'
      }
      xhrf('bcontext', fData)
      const index = this.foodsaver.findIndex(member => member.id === fsId)
      if (index >= 0) {
        this.foodsaver.splice(index, 1)
        this.$refs.teamlist.refresh()
      }
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
        phone: fs.telefon,
        joinDate: fs.add_date ? fromUnixTime(fs.add_date) : null,
        lastPickup: fs.last_fetch ? fromUnixTime(fs.last_fetch) : null,
        fetchCount: fs.stat_fetchcount
      }
    }
  }
}
</script>

<style lang="scss" scoped>
// separate because of loader issues with deep selectors in scoped + nested SCSS
// (see https://github.com/vuejs/vue-loader/issues/913 for a discussion)
.store-team .col-ava .member-pic ::v-deep img {
  width: 50px;
  height: 50px;
  border-radius: 6px;
  overflow: hidden;
}
</style>

<style lang="scss" scoped>
.store-team .team-list.bootstrap {
  --fetchcount-bg: var(--fs-beige);
  --fetchcount-fg: var(--fs-brown);
  --fetchcount-border: var(--fs-brown);
  --role-may-manage-store: var(--fs-green);
  --role-may-ambassador: var(--warning);
  --role-other: var(--fs-beige);

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

      a {
        display: inline-block;
      }

      .member-pic.jumper {
        opacity: 0.5;
      }

      .member-fetchcount {
        position: absolute;
        top: 0;
        right: -10px;
        border: 2px solid var(--fetchcount-border);
        min-width: 1.5rem;
        opacity: 0.9;
        background-color: var(--fetchcount-bg);
        color: var(--fetchcount-fg);

        &.maysm {
          border-color: var(--role-may-manage-store);
        }
        // &.mayamb {
        //   border-color: var(--role-may-ambassador);
        // }
        &.waiting {
          border-color: var(--role-other);
        }
      }
    }

    &.col-info {
      flex-grow: 1;

      .member-info {
        display: flex;
        min-height: 50px;
        padding-left: 10px;
        flex-direction: column;
        justify-content: center;
        font-size: smaller;
        color: var(--dark);

        &:hover, &:focus {
          text-decoration: none;
          outline-color: var(--fs-brown);
        }
      }

      .member-name {
        padding-left: 1px;
        min-width: 0;
        word-break: break-word;
        font-weight: bolder;
      }
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
