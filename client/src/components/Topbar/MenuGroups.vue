<template>
  <nav-item-dropdown
    v-if="workingGroups.length"
    tooltip="Deine Gruppen"
    no-caret
  >
    <template slot="button-content">
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
          <i class="far fa-comment" /> Forum
        </a>
        <a
          :href="$url('wall', group.id)"
          role="menuitem"
          class="dropdown-item"
        >
          <i class="fas fa-bullhorn" /> Pinnwand
        </a>
        <a
          :href="$url('events', group.id)"
          role="menuitem"
          class="dropdown-item"
        >
          <i class="far fa-calendar-alt" /> Termine
        </a>
        <a
          v-if="group.isBot"
          :href="$url('workingGroupEdit', group.id)"
          role="menuitem"
          class="dropdown-item"
        >
          <i class="fas fa-cog" /> Gruppe verwalten
        </a>
      </b-collapse>
    </div>
    <div class="dropdown-divider" />
    <a
      :href="$url('workingGroups')"
      role="menuitem"
      class="dropdown-item"
    >
      <small><i class="fas fa-users" /> Gruppenübersicht</small>
    </a>
  </nav-item-dropdown>
  <li
    v-else
    class="nav-item"
  >
    <a
      v-b-tooltip
      :href="$url('workingGroups')"
      class="nav-link"
      title="Gruppenübersicht"
    >
      <i class="fas fa-users" />
    </a>
  </li>
</template>
<script>
import bCollapse from '@b/components/collapse/collapse'
import bToggle from '@b/directives/toggle/toggle'
import bTooltip from '@b/directives/tooltip/tooltip'
import NavItemDropdown from './NavItemDropdown'

export default {
  components: { bCollapse, NavItemDropdown },
  directives: { bToggle, bTooltip },
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
