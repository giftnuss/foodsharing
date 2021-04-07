<template>
  <div class="rounded toggle-status p-2 mb-2">
    <span class="legend font-italic">
      {{ $i18n('forum.follow.header') }}
    </span>
    <div class="d-lg-inline-block">
      <b-form-checkbox
        :checked="isFollowingBell"
        class="bell"
        switch
        @change="$emit('toggle:follow-bell')"
      >
        <a :class="{'text-strike': true, 'enabled': isFollowingBell}">
          {{ $i18n('forum.follow.bell') }}
        </a>
      </b-form-checkbox>
      <b-form-checkbox
        :checked="isFollowingEmail"
        class="email"
        switch
        @change="$emit('toggle:follow-email')"
      >
        <a :class="{'text-strike': true, 'enabled': isFollowingEmail}">
          {{ $i18n('forum.follow.email') }}
        </a>
      </b-form-checkbox>
      <b-form-checkbox
        v-if="mayModerate"
        :checked="isSticky"
        class="sticky"
        switch
        @change="$emit('toggle:sticky')"
      >
        <a :class="{'text-bold enabled': isSticky}">
          {{ $i18n('forum.thread.stick') }}
        </a>
      </b-form-checkbox>
      <b-button
        v-if="mayModerate && isOpen"
        small
        class="ml-2 btn-sm"
        data-toggle="tooltip"
        data-placement="bottom"
        @click="$emit('close')"
      >
        {{ $i18n('forum.thread.close') }}
      </b-button>
      <b-button
        v-if="mayModerate && !isOpen"
        small
        class="ml-2 btn-sm"
        data-toggle="tooltip"
        data-placement="bottom"
        @click="$emit('open')"
      >
        {{ $i18n('forum.thread.open') }}
      </b-button>
    </div>
  </div>
</template>

<script>

import ThreadStatus from './ThreadStatus'

export default {
  components: {},
  props: {
    isFollowingBell: { type: Boolean, default: null },
    isFollowingEmail: { type: Boolean, default: null },
    isSticky: { type: Boolean, default: null },
    mayModerate: { type: Boolean, default: false },
    status: { type: Number, default: ThreadStatus.THREAD_OPEN },
  },
  computed: {
    isOpen () {
      return this.currentStatus === ThreadStatus.THREAD_OPEN
    },
  },
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
