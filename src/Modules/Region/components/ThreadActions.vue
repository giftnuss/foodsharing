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
        v-if="showSticky"
        :checked="isSticky"
        class="sticky"
        switch
        @change="$emit('toggle:sticky')"
      >
        <a :class="{'text-bold enabled': isSticky}">
          {{ $i18n('forum.thread.stick') }}
        </a>
      </b-form-checkbox>
    </div>
    <div>
      <div>{{ status }}, {{ ThreadStatus.THREAD_OPEN }}, {{ ThreadStatus.THREAD_CLOSED }}</div>
      <b-button
        v-if="status === ThreadStatus.THREAD_OPEN"
        data-toggle="tooltip"
        data-placement="bottom"
        :title="$i18n('forum.thread.close')"
        @click="$emit('close')"
      >
        <i class="fas fa-folder" />
      </b-button>
      <b-button
        v-else
        data-toggle="tooltip"
        data-placement="bottom"
        :title="$i18n('forum.thread.open')"
        @click="$emit('open')"
      >
        <i class="fas fa-folder-open" />
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
    showSticky: { type: Boolean, default: null },
    status: { type: Number, default: ThreadStatus.THREAD_OPEN },
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
