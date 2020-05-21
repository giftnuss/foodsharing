<template>
  <!--
  Default team list order:
  - store managers
  - active team members
  - jumpers
  Sleeping team members will come last in each of those sections
  -->
  <div class="store-team">
    <div
      v-if="storeTitle"
      class="head ui-widget-header ui-corner-top"
    >
      {{ $i18n('store.teamName', { storeTitle }) }}
    </div>
    <div class="corner-bottom margin-bottom bootstrap">
      <b-list-group :class="['team', `team-${storeId}`]">
        <b-list-group-item
          v-for="foo in foodsaver"
          :key="foo.id"
          class="d-flex justify-content-between member"
          :class="[`fs-${foo.id}`, {
            'jumper': foo.isJumper,
            'contextmenu-team': mayEditStore && foo.isActive,
            'contextmenu-jumper': mayEditStore && foo.isJumper }]"
          :variant="foo.isManager ? 'warning' : ''"
          button
        >
          <b-tooltip :target="`member-${foo.id}`">
            <div v-if="foo.isManager">
              {{ $i18n('store.isManager', { name: foo.name || '' }) }}
            </div>
            <div v-if="foo.joinDate">
              {{ $i18n('store.memberSince', { date: $dateFormat(foo.joinDate, 'day') }) }}
            </div>
            <div v-if="foo.fetchCount && foo.lastPickup">
              {{ $i18n('store.lastPickup', { date: $dateFormat(foo.lastPickup, 'day') }) }}
            </div>
            <div v-else-if="mayEditStore">
              {{ $i18n('store.noPickup') }}
            </div>
          </b-tooltip>

          <a :href="`/profile/${foo.id}`">
            <Avatar
              :url="foo.avatar"
              :size="50"
              class="member-pic"
              :sleep-status="foo.sleepStatus"
            />
          </a>
          <b-badge
            class="member-fetchcount"
            :class="foo.roleColorClass"
            tag="span"
            variant="primary"
            :pill="foo.fetchCount < 100"
          >
            <span v-if="foo.isJumper">
              <i class="fas fa-star member-jumper" />
            </span>
            <span v-else>{{ foo.fetchCount }}</span>
          </b-badge>
          <div
            :id="`member-${foo.id}`"
            v-b-tooltip.hover
            class="member-info flex-grow-1 flex-shrink-0"
          >
            <span class="member-name">
              {{ foo.name }}
            </span>
            <span class="member-phone">
              {{ foo.number }}
            </span>
            <span
              v-if="foo.phone && (foo.phone !== foo.number)"
              class="member-phone"
            >
              {{ foo.phone }}
            </span>
          </div>

          <div class="d-md-none member-teaminfo-mobile">
            <div v-if="foo.joinDate">
              {{ $i18n('store.memberSince', { date: $dateFormat(foo.joinDate, 'day') }) }}
            </div>
            <div v-if="foo.fetchCount && foo.lastPickup">
              {{ $i18n('store.lastPickup', { date: $dateFormat(foo.lastPickup, 'day') }) }}
            </div>
            <div v-else-if="mayEditStore">
              {{ $i18n('store.noPickup') }}
            </div>
          </div>

          <!-- TODO isMobile instead of media query? -->
          <b-button
            v-if="foo.callable"
            variant="link"
            class="member-call d-md-none"
            :href="foo.callable"
          >
            <i class="fas fa-phone" />
          </b-button>
        </b-list-group-item>
      </b-list-group>
    </div>
  </div>
</template>

<script>
import _ from 'underscore'
import fromUnixTime from 'date-fns/fromUnixTime'
import { callableNumber } from '@/utils'
import Avatar from '@/components/Avatar'

export default {
  components: { Avatar },
  props: {
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
    foodsaverData (fs) {
      const roleColorClass = {
        1: 'fs', // Role::FOODSAVER
        2: 'sm', // Role::STORE_MANAGER
        3: 'amb' // Role::AMBASSADOR
        // 4: 'orga' // Role::ORGA
      }[fs.quiz_rolle] || ''
      return {
        id: fs.id,
        isActive: fs.team_active === 1, // MembershipStatus::MEMBER
        isJumper: fs.team_active === 2, // MembershipStatus::JUMPER
        isManager: !!fs.verantwortlich,
        // isVerified: fs.verified === 1, // ?!
        avatar: fs.photo,
        roleColorClass,
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
.store-team {
  --role-ambassador: #fa0; // darker version of --warning
  --role-storemanager: var(--fs-green);
  --role-foodsaver: var(--fs-brown);

  .list-group.team {
    padding-top: 0px;
  }

  .list-group-item.member {
    padding: 2px;
    padding-right: 0;

    &:not(:last-of-type) {
      border-bottom: 0;
    }

    &:focus {
      outline: 0;
    }

    &.jumper {
      opacity: 0.8;

      .member-pic { opacity: 0.6; }
    }

    .member-pic {
      // vertical-align: top;
    }
    .member-pic /deep/ img {
      border-radius: 6px;
    }

    .member-fetchcount {
      position: absolute;
      top: -2px;
      left: 34px;
      border: 2px solid var(--white);
      min-width: 1.5rem;
      opacity: 0.9;

      &.fs { background-color: var(--role-foodsaver); }
      &.sm { background-color: var(--role-storemanager); }
      &.amb { background-color: var(--role-ambassador); }
    }

    .member-info {
      display: inline-flex;
      flex-direction: column;
      padding-left: 9px;
      max-width: calc(100% - 50px - 9px);
      min-width: 0;
      align-self: center;
      font-size: smaller;
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
    }
  }
}
</style>
