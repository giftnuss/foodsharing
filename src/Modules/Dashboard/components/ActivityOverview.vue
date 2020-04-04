<template>
  <div class="activity-container">
    <div class="head ui-widget-header ui-rectangular-bottom activities">
      {{ $i18n('dashboard.updates_title') }}
      <div class="dashboard-options">
        <a
          :class="{'active': JSON.stringify(displayedTypes) === JSON.stringify(['store', 'forum', 'mailbox', 'foodsharepoint', 'friendWall', 'event'])}"
          class="wide"
          @click="displayAll"
        >
          <!-- For alignment, make sure there is no additional whitespace inside the span! -->
          <span>{{ $i18n('dashboard.display_all') }}</span>
        </a>
        <div class="header-divider" />
        <a
          :class="{'active': JSON.stringify(displayedTypes) === JSON.stringify(['forum'])}"
          class="fa-fw fas fa-comments"
          @click="displayOne('forum')"
        />
        <a
          :class="{'active': JSON.stringify(displayedTypes) === JSON.stringify(['event'])}"
          class="fa-fw far fa-calendar-alt"
          @click="displayOne('event')"
        />
        <a
          :class="{'active': JSON.stringify(displayedTypes) === JSON.stringify(['friendWall'])}"
          class="fa-fw fas fa-user"
          @click="displayOne('friendWall')"
        />
        <a
          :class="{'active': JSON.stringify(displayedTypes) === JSON.stringify(['foodsharepoint'])}"
          class="fa-fw fas fa-recycle"
          @click="displayOne('foodsharepoint')"
        />
        <a
          :class="{'active': JSON.stringify(displayedTypes) === JSON.stringify(['mailbox'])}"
          class="fa-fw fas fa-envelope"
          @click="displayOne('mailbox')"
        />
        <a
          :class="{'active': JSON.stringify(displayedTypes) === JSON.stringify(['store'])}"
          class="fa-fw fas fa-shopping-cart"
          @click="displayOne('store')"
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

export default {
  components: { ActivityThread, ActivityOptionListings },
  props: {},
  data () {
    return {
      displayedTypes: ['store', 'forum', 'mailbox', 'foodsharepoint', 'friendWall', 'event'],
      showListings: false
    }
  },
  methods: {
    displayOne: function (type) {
      this.displayedTypes = [type]
      this.$refs.thread.resetInfinity()
    },
    displayAll: function () {
      this.displayedTypes = ['store', 'forum', 'mailbox', 'foodsharepoint', 'friendWall', 'event']
      this.$refs.thread.resetInfinity()
    },
    toggleOptionListings: function () {
      this.showListings = !this.showListings
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
