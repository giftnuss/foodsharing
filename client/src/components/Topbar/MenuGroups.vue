<template>
  <div>
    <fs-dropdown-menu
      v-if="workingGroups.length"
      id="dropdown-groups"
      menu-title="menu.entry.your_groups"
      :show-menu-title="false"
      icon="fa-users"
    >
      <div
        v-for="group in workingGroups"
        :key="group.id"
        class="group"
      >
        <a
          v-if="!alwaysOpen"
          v-b-toggle="`topbargroup_${group.id}`"
          role="menuitem"
          class="dropdown-header dropdown-item text-truncate"
          href="#"
          target="_self"
        >
          {{ group.name }}
        </a>
        <h3
          v-if="alwaysOpen"
          role="menuitem"
          class="dropdown-header text-truncate"
        >
          {{ group.name }}
        </h3>
        <b-collapse
          :id="`topbargroup_${group.id}`"
          :visible="alwaysOpen"
          :accordion="alwaysOpen ? null : 'groups'"
        >
          <a
            :href="$url('forum', group.id)"
            role="menuitem"
            class="dropdown-item sub"
          >
            <i class="far fa-comment-alt" /> {{ $i18n('menu.entry.forum') }}
          </a>
          <a
            :href="$url('wall', group.id)"
            role="menuitem"
            class="dropdown-item sub"
          >
            <i class="fas fa-bullhorn" /> {{ $i18n('menu.entry.wall') }}
          </a>
          <a
            :href="$url('events', group.id)"
            role="menuitem"
            class="dropdown-item sub"
          >
            <i class="far fa-calendar-alt" /> {{ $i18n('menu.entry.events') }}
          </a>
          <a
            :href="$url('members', group.id)"
            role="menuitem"
            class="dropdown-item sub"
          >
            <i class="fas fa-user" /> {{ $i18n('menu.entry.members') }}
          </a>
          <a
            href="#"
            role="menuitem"
            class="dropdown-item sub"
            @click="showConferencePopup(group.id)"
          >
            <i class="fas fa-users" /> {{ $i18n('menu.entry.conference') }}
          </a>
          <a
            :href="$url('polls', group.id)"
            role="menuitem"
            class="dropdown-item sub"
          >
            <i class="fas fa-poll-h" /> {{ $i18n('terminology.polls') }}
          </a>
          <a
            v-if="group.isBot"
            :href="$url('workingGroupEdit', group.id)"
            role="menuitem"
            class="dropdown-item sub"
          >
            <i class="fas fa-cog" /> {{ $i18n('menu.entry.workingGroupEdit') }}
          </a>
        </b-collapse>
      </div>
      <div class="dropdown-divider" />
      <a
        :href="$url('workingGroups')"
        role="menuitem"
        class="dropdown-item"
      >
        <small><i class="fas fa-users" /> {{ $i18n('menu.entry.groups') }}</small>
      </a>
    </fs-dropdown-menu>
    <menu-item
      v-else
      :url="$url('workingGroups')"
      icon="fa-users"
      :title="$i18n('menu.entry.groups')"
      :hide-title-mobile="true"
    />
  </div>
</template>
<script>
import { BCollapse, VBToggle, VBTooltip } from 'bootstrap-vue'
import Conference from './Conference'
import FsDropdownMenu from './FsDropdownMenu'
import MenuItem from './MenuItem'

export default {
  components: { BCollapse, FsDropdownMenu, MenuItem },
  directives: { VBToggle, VBTooltip },
  mixins: [Conference],
  props: {
    workingGroups: {
      type: Array,
      default: () => [],
    },
  },
  computed: {
    alwaysOpen () {
      return this.workingGroups.length <= 2
    },
  },
}
</script>
