<template>
  <div class="activity-container">
    <div class="head ui-widget-header ui-rectangular-bottom">
      Updates-Übersicht
      <span class="option">
        <a
          class="wide"
          :class="{'active': JSON.stringify(displayedTypes) === JSON.stringify(['store', 'forum', 'mailbox', 'friendWall', 'foodbasket'])}"
          @click="displayAll"
        >
          Alle
        </a>
        <div class="headerDivider" />
        <a
          class="fa-fw fas fa-comments"
          :class="{'active': JSON.stringify(displayedTypes) === JSON.stringify(['forum'])}"
          @click="displayOne('forum')"
        />
        <a
          class="fa-fw fas fa-shopping-basket"
          :class="{'active': JSON.stringify(displayedTypes) === JSON.stringify(['foodbasket'])}"
          @click="displayOne('foodbasket')"
        />
        <a
          class="fa-fw fas fa-user"
          :class="{'active': JSON.stringify(displayedTypes) === JSON.stringify(['friendWall'])}"
          @click="displayOne('friendWall')"
        />
        <a
          class="fa-fw fas fa-envelope"
          :class="{'active': JSON.stringify(displayedTypes) === JSON.stringify(['mailbox'])}"
          @click="displayOne('mailbox')"
        />
        <a
          class="fa-fw fas fa-shopping-cart"
          :class="{'active': JSON.stringify(displayedTypes) === JSON.stringify(['store'])}"
          @click="displayOne('store')"
        />
        <div class="headerDivider" />
        <a
          id="activity-option"
          :class="{'active': showListings}"
          class="fas fa-cog"
          @click="toggleOptionListings"
        />
      </span>
    </div>
    <div
      v-if="showListings"
      class="ui-widget-content corner-bottom margin-bottom ui-padding"
    >
      <ActivityOptionListings @close="showListings = false" />
    </div>
    <ActivityThread
      id="activity"
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
      displayedTypes: ['store', 'forum', 'mailbox', 'friendWall', 'foodbasket'],
      showListings: false
    }
  },
  methods: {
    displayOne: function (type) {
      this.displayedTypes = [type]
      // this.showListings = false
    },
    displayAll: function () {
      this.displayedTypes = ['store', 'forum', 'mailbox', 'friendWall', 'foodbasket']
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
.headerDivider {
     border-left: 1px solid #cfcfcf;
     height: 15px;
     width: 1px;
     display: inline-block;
     vertical-align: middle;
     margin: 0 6px;
}
.option {
  cursor: pointer;
}
.option .active {
  color: #4A3520;
  background-color: white;
}
.option .wide {
  height: inherit;
  width: inherit;
  border-radius: 4px;
}
.option .wide.active {
  padding: 4px;
}
</style>
