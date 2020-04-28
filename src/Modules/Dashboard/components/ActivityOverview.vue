<template>
  <div class="activity-container">
    <div class="head ui-widget-header ui-rectangular-bottom activities">
      <span v-if="currentFilterDescription">
        {{ $i18n('dashboard.updates_title_some', [$i18n(currentFilterDescription)]) }}
      </span>
      <span v-else>
        {{ $i18n('dashboard.updates_title_all') }}
      </span>
      <div class="dashboard-options">
        <a
          :class="{'active': isActiveFilter()}"
          class="wide"
          @click="filter()"
        >
          <!-- For alignment, make sure there is no additional whitespace inside the span! -->
          <span>{{ $i18n('dashboard.display_all') }}</span>
        </a>
        <div class="header-divider" />
        <a
          :class="{'active': isActiveFilter('forum')}"
          class="fa-fw fas fa-comment-alt"
          @click="filter('forum')"
        />
        <a
          :class="{'active': isActiveFilter('event')}"
          class="fa-fw far fa-calendar-alt"
          @click="filter('event')"
        />
        <a
          :class="{'active': isActiveFilter('friendWall')}"
          class="fa-fw fas fa-user"
          @click="filter('friendWall')"
        />
        <a
          :class="{'active': isActiveFilter('foodsharepoint')}"
          class="fa-fw fas fa-recycle"
          @click="filter('foodsharepoint')"
        />
        <a
          :class="{'active': isActiveFilter('mailbox')}"
          class="fa-fw fas fa-envelope"
          @click="filter('mailbox')"
        />
        <a
          :class="{'active': isActiveFilter('store')}"
          class="fa-fw fas fa-shopping-cart"
          @click="filter('store')"
        />
        <div class="header-divider" />
        <a
          id="activity-option"
          :class="{'active': showListings}"
          class="fas fa-cog"
          @click="toggleOptionListings"
        />
      </div>
    </div>
    <div
      v-if="showListings"
      class="ui-widget-content corner-bottom margin-bottom ui-padding options-content"
    >
      <ActivityOptionListings
        @close="showListings = false"
        @reloadData="$refs.thread.reloadData()"
      />
    </div>
    <ActivityThread
      id="activity"
      ref="thread"
      :displayed-types="displayedTypes"
    />
  </div>
</template>

<script>
import ActivityThread from './ActivityThread'
import ActivityOptionListings from './ActivityOptionListings'
import { allFilterTypes } from './ActivityFilter'
import _ from 'underscore'

export default {
  components: { ActivityThread, ActivityOptionListings },
  props: {
    allTypes: {
      type: Array,
      default: () => { return allFilterTypes }
    }
  },
  data () {
    return {
      displayedTypes: this.allTypes,
      showListings: false
    }
  },
  computed: {
    currentFilterDescription () {
      if (this.displayedTypes.length === 1) {
        switch (this.displayedTypes[0]) {
          case 'event': return 'terminology.events'
          case 'forum': return 'terminology.forum'
          case 'foodsharepoint': return 'terminology.fsp'
          case 'friendWall': return 'terminology.wall'
          case 'mailbox': return 'terminology.mailboxes'
          case 'store': return 'terminology.stores'
          default:
            return null
        }
      } else {
        // this assumes that no other filter enables more than one type!
        return null
      }
    }
  },
  methods: {
    filter: function (category = null) {
      const categories = category ? [category] : this.allTypes
      this.displayedTypes = categories
      this.$refs.thread.resetInfinity()
    },
    toggleOptionListings: function () {
      this.showListings = !this.showListings
    },
    isActiveFilter: function (category = null) {
      const categories = category ? [category] : this.allTypes
      return _.isEqual(categories, this.displayedTypes)
    }
  }
}
</script>

<style lang="scss" scoped>
.activity-container {
  margin-bottom: 1em;
}

.activities {
  display: flex;
  flex-wrap: wrap;
  justify-content: space-between;
}

.dashboard-options {
  cursor: pointer;

  > a {
    padding: 2px;
    border-radius: 50%;
    height: 18px;
    width: 18px;
    line-height: 18px;
    text-align: center;
    text-decoration: none;

    &:hover,
    &.active {
      color: var(--fs-brown);
      background-color: var(--fs-white);
    }

    &.wide {
      border-radius: 4px;

      span {
        padding: 2px 4px;
        font-weight: bolder;
      }
    }
  }

  .header-divider {
    border-left: 1px solid var(--fs-beige);
    height: 18px;
    width: 1px;
    display: inline-block;
    vertical-align: middle;
    margin: 0 6px;
  }
}
</style>
