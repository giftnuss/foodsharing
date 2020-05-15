<template>
  <div>
    <b-nav-item-dropdown
      v-if="workingGroups.length"
      id="dropdown-groups"
      v-b-tooltip="$i18n('menu.entry.your_groups')"
      no-caret
    >
      <template v-slot:button-content>
        <i class="fas fa-users" />
      </template>
      <div
        v-for="group in workingGroups"
        :key="group.id"
        class="group"
      >
        <a
          v-if="!alwaysOpen"
          v-b-toggle="`topbargroup_${group.id}`"
          role="menuitem"
          class="dropdown-item text-truncate"
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
          class="sub"
        >
          <a
            :href="$url('forum', group.id)"
            role="menuitem"
            class="dropdown-item"
          >
            <i class="far fa-comment-alt" /> {{ $i18n('menu.entry.forum') }}
          </a>
          <a
            :href="$url('wall', group.id)"
            role="menuitem"
            class="dropdown-item"
          >
            <i class="fas fa-bullhorn" /> {{ $i18n('menu.entry.wall') }}
          </a>
          <a
            :href="$url('events', group.id)"
            role="menuitem"
            class="dropdown-item"
          >
            <i class="far fa-calendar-alt" /> {{ $i18n('menu.entry.events') }}
          </a>
          <a
            :href="$url('members', group.id)"
            role="menuitem"
            class="dropdown-item"
          >
            <i class="fas fa-user" /> {{ $i18n('menu.entry.members') }}
          </a>
          <a
            v-if="group.isBot"
            :href="$url('workingGroupEdit', group.id)"
            role="menuitem"
            class="dropdown-item"
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
    </b-nav-item-dropdown>
    <li
      v-else
      v-b-tooltip
      :title="$i18n('menu.entry.groups')"
      class="nav-item"
    >
      <a
        v-b-tooltip
        :title="$i18n('menu.entry.groups')"
        :href="$url('workingGroups')"
        class="nav-link"
      >
        <i class="fas fa-users" />
      </a>
    </li>
  </div>
</template>
<script>
import { BCollapse, VBToggle, VBTooltip } from 'bootstrap-vue'

export default {
  components: { BCollapse },
  directives: { VBToggle, VBTooltip },
  props: {
    workingGroups: {
      type: Array,
      default: () => []
    }
  },
  computed: {
    alwaysOpen () {
      return this.workingGroups.length <= 2
    }
  }
}
</script>

<style lang="scss" scoped>
.dropdown-header {
    font-weight: bold;
    font-size: 0.9em;
}
</style>
