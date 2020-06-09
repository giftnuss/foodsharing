<template>
  <!--
  Default team list order:
  - store managers
  - active team members
  - jumpers
  Sleeping team members will come last in each of those sections
  -->
  <div :class="['store-team', `team-${storeId}`]">
    <div
      v-if="storeTitle"
      class="head ui-widget-header ui-corner-top"
    >
      {{ $i18n('store.teamName', { storeTitle }) }}
    </div>
    <div class="corner-bottom margin-bottom bootstrap team-list">
      <b-table
        ref="teamlist"
        :items="foodsaver"
        :fields="['ava', 'info', `${(wXS || wSM) ? 'mobinfo' : ''}`, `${(wXS || wSM) ? 'call' : ''}`]"
        primary-key="id"
        thead-class="d-none"
      >
        <template v-slot:cell(ava)="data">
          <div class="member-ava">
            <!-- eslint-disable-next-line vue/max-attributes-per-line -->
            <a :href="`/profile/${data.item.id}`" tabindex="-1">
              <Avatar
                :url="data.item.avatar"
                :size="50"
                :class="{'member-pic': true, 'jumper': data.item.isJumper}"
                :sleep-status="data.item.sleepStatus"
              />
            </a>
            <b-badge
              class="member-fetchcount"
              tag="span"
              variant="primary"
              :pill="data.item.fetchCount < 100"
            >
              <span v-if="data.item.isJumper">
                <i class="fas fa-star member-jumper" />
              </span>
              <span v-else>{{ data.item.fetchCount }}</span>
            </b-badge>
          </div>
        </template>

        <template v-slot:cell(info)="data">
          <b-tooltip
            :target="`member-${data.item.id}`"
            triggers="hover blur"
            :variant="`${data.item.isManager ? 'warning' : (data.item.isJumper ? 'secondary' : '')}`"
          >
            <div v-if="data.item.isManager">
              {{ $i18n('store.isManager', { name: data.item.name || '' }) }}
            </div>
            <div v-if="data.item.joinDate">
              {{ $i18n('store.memberSince', { date: $dateFormat(data.item.joinDate, 'day') }) }}
            </div>
            <div v-if="data.item.fetchCount && data.item.lastPickup">
              {{ $i18n('store.lastPickup', { date: $dateFormat(data.item.lastPickup, 'day') }) }}
            </div>
            <div v-else>
              {{ $i18n('store.noPickup') }}
            </div>
            <div v-if="data.item.isJumper">
              {{ $i18n('store.isJumper') }}
            </div>
            <div v-if="!data.item.isVerified">
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
          </a>
        </template>

        <template v-slot:cell(mobinfo)="data">
          <div class="member-teaminfo-mobile">
            <div v-if="data.item.joinDate">
              {{ $i18n('store.memberSince', { date: $dateFormat(data.item.joinDate, 'day') }) }}
            </div>
            <div v-if="data.item.fetchCount && data.item.lastPickup">
              {{ $i18n('store.lastPickup', { date: $dateFormat(data.item.lastPickup, 'day') }) }}
            </div>
            <div v-else-if="mayEditStore">
              {{ $i18n('store.noPickup') }}
            </div>
          </div>
        </template>

        <template v-slot:cell(call)="data">
          <b-button
            v-if="data.item.callable"
            variant="link"
            class="member-call"
            :href="data.item.callable"
          >
            <i class="fas fa-phone" />
          </b-button>
        </template>

        <template v-slot:row-details="data">
          <div class="member-actions">
            <b-button
              size="sm"
              :href="`/profile/${data.item.id}`"
              :block="!(wXS || wSM)"
            >
              <i class="fas fa-user" />
              {{ $i18n('pickup.open_profile') }}
            </b-button>

            <b-button
              v-if="data.item.id !== fsId"
              size="sm"
              variant="secondary"
              :block="!(wXS || wSM)"
              @click="openChat(data.item.id)"
            >
              <i class="fas fa-comment" />
              {{ $i18n('chat.open_chat') }}
            </b-button>

            <b-button
              v-if="mayEditStore && data.item.isJumper"
              size="sm"
              variant="primary"
              :block="!(wXS || wSM)"
              @click="changeMembershipStatus(data.item.id, 'toteam')"
            >
              <i class="fas fa-clipboard-check" />
              {{ $i18n('store.makeRegularTeamMember') }}
            </b-button>

            <b-button
              v-if="mayEditStore && data.item.isActive"
              size="sm"
              variant="primary"
              :block="!(wXS || wSM)"
              @click="changeMembershipStatus(data.item.id, 'tojumper')"
            >
              <i class="fas fa-mug-hot" />
              {{ $i18n('store.makeJumper') }}
            </b-button>

            <b-button
              v-if="mayEditStore"
              size="sm"
              variant="danger"
              :block="!(wXS || wSM)"
              @click="removeFromTeam(data.item.id)"
            >
              <i class="fas fa-user-times" />
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

export default {
  components: { Avatar },
  mixins: [MediaQueryMixin],
  props: {
    fsId: { type: Number, required: true },
    mayEditStore: { type: Boolean, default: false },
    team: { type: Array, required: true },
    storeId: { type: Number, required: true },
    storeTitle: { type: String, default: '' }
  },
  computed: {
    foodsaver () {
      return _.map(this.team, this.foodsaverData)
    }
  },
  methods: {
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
    removeFromTeam (fsId) {
      if (!confirm(i18n('are_you_sure'))) {
        return
      }
      const fData = {
        bid: this.storeId,
        fsid: fsId,
        action: 'delete'
      }
      xhrf('bcontext', fData)
      const index = this.team.findIndex(fs => fs.id === fsId)
      if (index >= 0) {
        this.foodsaver.splice(index, 1)
        this.$refs.teamlist.refresh()
      }
    },
    foodsaverData (fs) {
      return {
        id: fs.id,
        isActive: fs.team_active === 1, // MembershipStatus::MEMBER
        isJumper: fs.team_active === 2, // MembershipStatus::JUMPER
        isManager: !!fs.verantwortlich,
        _rowVariant: fs.verantwortlich ? 'warning' : (fs.verified ? '' : 'primary'),
        isVerified: fs.verified === 1,
        avatar: fs.photo,
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
.store-team .member-ava .member-pic ::v-deep img {
  width: 50px;
  height: 50px;
  border-radius: 6px;
  overflow: hidden;
}
</style>

<style lang="scss" scoped>
.store-team .team-list {
  background: var(--white);
}

.store-team ::v-deep table tr td {
  padding: 2px;
  border-top-color: var(--border);
  vertical-align: middle;
  cursor: default;

  .jumper {
    opacity: 0.75;

    &.member-pic {
      opacity: 0.5;
    }
  }

  .member-ava {
    position: relative;

    .member-fetchcount {
      position: absolute;
      top: -2px;
      left: 36px;
      border: 2px solid var(--white);
      background-color: var(--fs-brown);
      min-width: 1.5rem;
      opacity: 0.9;
    }
  }

  .member-info {
    display: flex;
    min-height: 50px;
    padding-left: 9px;
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

  .member-teaminfo-mobile {
    align-self: center;
    padding: 0 10px;
    text-align: right;

    &, div {
      font-size: smaller;
    }
  }

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
  }

  .member-actions {
    .btn {
      margin-bottom: 5px;
    }
  }
}
</style>
