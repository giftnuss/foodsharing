<template>
  <div class="rounded toggle-status p-2 mb-2">
    <span class="legend font-italic">
      {{ $i18n('forum.follow.header') }}
    </span>
    <div class="d-inline-block">
      <b-form-checkbox
        :checked="isFollowingBell"
        class="bell"
        switch
        @change="toggleFollowBell"
      >
        <a :class="{'text-strike': true, 'enabled': isFollowingBell}">
          {{ $i18n('forum.follow.bell') }}
        </a>
      </b-form-checkbox>
      <b-form-checkbox
        :checked="isFollowingEmail"
        class="email"
        switch
        @change="toggleFollowEmail"
      >
        <a :class="{'text-strike': true, 'enabled': isFollowingEmail}">
          {{ $i18n('forum.follow.email') }}
        </a>
      </b-form-checkbox>
      <b-form-checkbox
        v-if="showSticky"
        :checked="isSticky"
        class="sticky"
        switch
        @change="toggleStickyness"
      >
        <a :class="{'text-bold enabled': isSticky}">
          {{ $i18n('forum.thread.stick') }}
        </a>
      </b-form-checkbox>
    </div>
  </div>
</template>

<script>

import * as api from '@/api/forum'
import { pulseError } from '@/script'
import i18n from '@/i18n'

export default {
  components: {},
  props: {
    threadId: { type: Number, default: null },
    isFollowingBell: { type: Boolean, default: null },
    isFollowingEmail: { type: Boolean, default: null },
    isSticky: { type: Boolean, default: null },
    showSticky: { type: Boolean, default: null }
  },
  methods: {
    async toggleFollowBell () {
      let targetState = !this.isFollowingBell
      try {
        if (targetState) {
          await api.followThreadByBell(this.threadId)
        } else {
          await api.unfollowThreadByBell(this.threadId)
        }
        // this.$emit('update:bell')
      } catch (err) {
        // failed? undo it
        targetState = !targetState
        pulseError(i18n('error_unexpected'))
      } finally {
        this.$emit('update:isFollowingBell', targetState)
      }
    },
    async toggleFollowEmail () {
      let targetState = !this.isFollowingEmail
      try {
        if (targetState) {
          await api.followThreadByEmail(this.threadId)
        } else {
          await api.unfollowThreadByEmail(this.threadId)
        }
        // this.$emit('update:email')
      } catch (err) {
        // failed? undo it
        targetState = !targetState
        pulseError(i18n('error_unexpected'))
      } finally {
        this.$emit('update:isFollowingEmail', targetState)
      }
    },
    async toggleStickyness () {
      let targetState = !this.isSticky
      try {
        if (targetState) {
          await api.stickThread(this.threadId)
        } else {
          await api.unstickThread(this.threadId)
        }
        // this.$emit('update:sticky')
      } catch (err) {
        // failed? undo it
        targetState = !targetState
        pulseError(i18n('error_unexpected'))
      } finally {
        this.$emit('update:isSticky', targetState)
      }
    }
  }
}
</script>

<style lang="scss" scoped>
.toggle-status {
  a.text-bold.enabled {
    font-weight: bold;
  }
  a.text-strike:not(.enabled) {
    text-decoration: line-through;
  }

  .custom-switch {
    padding-left: 3rem;
  }

  ::v-deep .custom-control {
    display: inline-block;
    /* from bootstrap min-height */
    line-height: 1.5rem;

    .custom-control-label {
      line-height: unset;
    }
  }
}
</style>
