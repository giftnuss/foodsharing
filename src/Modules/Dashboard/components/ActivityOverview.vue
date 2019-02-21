<template>
  <div class="activity-container">
    <div class="head ui-widget-header ui-rectangular-bottom">
			Updates-Übersicht
      <span class="option">
        <a class="wide" :class="{'active': JSON.stringify(displayedTypes) === JSON.stringify(['store', 'forum', 'mailbox', 'friendWall', 'foodbasket'])}" v-on:click="displayAll">Alle</a>
        <div class="headerDivider"></div>
        <a class="fa-fw fas fa-comments"        v-on:click="displayedTypes = ['forum']"      :class="{'active': JSON.stringify(displayedTypes) === JSON.stringify(['forum'])}"></a>
        <a class="fa-fw fas fa-shopping-basket" v-on:click="displayedTypes = ['foodbasket']" :class="{'active': JSON.stringify(displayedTypes) === JSON.stringify(['foodbasket'])}"></a>
        <a class="fa-fw fas fa-user"            v-on:click="displayedTypes = ['friendWall']" :class="{'active': JSON.stringify(displayedTypes) === JSON.stringify(['friendWall'])}"></a>
        <a class="fa-fw fas fa-envelope"        v-on:click="displayedTypes = ['mailbox']"    :class="{'active': JSON.stringify(displayedTypes) === JSON.stringify(['mailbox'])}"></a>
        <a class="fa-fw fas fa-shopping-cart"   v-on:click="displayedTypes = ['store']"      :class="{'active': JSON.stringify(displayedTypes) === JSON.stringify(['store'])}"></a>
        <div class="headerDivider"></div>
			  <a id="activity-option" :class="{'active': showListings}" v-on:click="toggleOptionListings" class="fas fa-cog"></a>
      </span>
		</div>
    <div v-if="showListings" class="ui-widget-content corner-bottom margin-bottom ui-padding">
      <ActivityOptionListings/>
    </div>
    <ActivityThread id="activity" :displayed-types="displayedTypes"/>
  </div>
</template>

<script>
import { getOptionListings } from "@/api/dashboard";
import ActivityThread from "./ActivityThread";
import ActivityOptionListings from "./ActivityOptionListings";

export default {
  components: { ActivityThread, ActivityOptionListings },
  props: {},
  data() {
    return {
      displayedTypes: ['store', 'forum', 'mailbox', 'friendWall', 'foodbasket'],
      showListings: false
    };
  },
  methods: {
    displayAll: function () {
      this.displayedTypes = ['store', 'forum', 'mailbox', 'friendWall', 'foodbasket']
    },
    toggleOptionListings: function () {
      this.showListings = !this.showListings
    },
  }
};
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
