<template>
  <fs-dropdown-menu
    id="dropdown-region"
    ref="dropdown"
    menu-title="terminology.regions"
    class="regionMenu"
    icon="fa-globe"
  >
    <template v-slot:heading-text>
      <span class="regionName text-truncate d-none d-sm-inline-block">
        {{ activeRegion ? activeRegion.name : $i18n('terminology.regions') }}
      </span>
    </template>
    <div
      v-for="region in regionsSorted"
      :key="region.id"
      class="group"
    >
      <a
        v-if="region.id !== activeRegionId || regions.length !== 1"
        v-b-toggle="`topbarregion_${region.id}`"
        role="menuitem"
        href="#"
        target="_self"
        class="dropdown-item text-truncate dropdown-header"
      >
        {{ region.name }}
      </a>
      <b-collapse
        :id="`topbarregion_${region.id}`"
        :visible="region.id === activeRegionId"
        accordion="regions"
      >
        <a
          :href="$url('forum', region.id)"
          role="menuitem"
          class="dropdown-item sub"
        >
          <i class="far fa-comment-alt" />{{ $i18n('menu.entry.forum') }}
        </a>
        <a
          v-if="region.isBot"
          :href="$url('forum', region.id, 1)"
          role="menuitem"
          class="dropdown-item sub"
        >
          <i class="far fa-comment-dots" />{{ $i18n('menu.entry.BOTforum') }}
        </a>
        <a
          :href="$url('foodsharepoints', region.id)"
          role="menuitem"
          class="dropdown-item sub"
        >
          <i class="fas fa-recycle" />{{ $i18n('terminology.fsp') }}
        </a>
        <a
          :href="$url('members', region.id)"
          role="menuitem"
          class="dropdown-item sub"
        >
          <i class="fas fa-user" />{{ $i18n('menu.entry.members') }}
        </a>
        <a
          :href="$url('events', region.id)"
          role="menuitem"
          class="dropdown-item sub"
        >
          <i class="far fa-calendar-alt" />{{ $i18n('menu.entry.events') }}
        </a>
        <a
          :href="$url('stores', region.id)"
          role="menuitem"
          class="dropdown-item sub"
        >
          <i class="fas fa-cart-plus" />{{ $i18n('menu.entry.stores') }}
        </a>
        <a
          :href="$url('workingGroups', region.id)"
          role="menuitem"
          class="dropdown-item sub"
        >
          <i class="fas fa-users" />{{ $i18n('terminology.groups') }}
        </a>
        <a
          :href="$url('statistic', region.id)"
          role="menuitem"
          class="dropdown-item sub"
        >
          <i class="fas fa-chart-bar" />{{ $i18n('terminology.statistic') }}
        </a>
        <a
          v-if="region.hasConference"
          href="#"
          role="menuitem"
          class="dropdown-item sub"
          @click="showConferencePopup(region.id)"
        >
          <i class="fas fa-users" />{{ $i18n('menu.entry.conference') }}
        </a>
        <a
          :href="$url('polls', region.id)"
          role="menuitem"
          class="dropdown-item sub"
        >
          <i class="fas fa-poll-h" />{{ $i18n('terminology.polls') }}
        </a>
        <a
          v-if="region.mayHandleFoodsaverRegionMenu"
          :href="$url('foodsaverList', region.id)"
          role="menuitem"
          class="dropdown-item sub"
        >
          <i class="fas fa-user" />{{ $i18n('menu.entry.fs') }}
        </a>
        <a
          v-if="region.isBot"
          :href="$url('passports', region.id)"
          role="menuitem"
          class="dropdown-item sub"
        >
          <i class="fas fa-address-card" />{{ $i18n('menu.entry.ids') }}
        </a>
        <a
          v-if="region.isBot"
          :href="$url('reports', region.id)"
          role="menuitem"
          class="dropdown-item sub"
        >
          <i class="fas fa-poo" />{{ $i18n('terminology.reports') }}
        </a>
      </b-collapse>
    </div>
    <div
      v-if="regionsSorted.length"
      class="dropdown-divider"
    />
    <a
      href="#"
      role="menuitem"
      class="dropdown-item"
      @click="joinRegionDialog"
    >
      <small><i class="fas fa-plus" /> {{ $i18n('menu.entry.joinregion') }}</small>
    </a>
  </fs-dropdown-menu>
</template>
<script>
import ui from '@/stores/ui'
import { BCollapse, VBToggle } from 'bootstrap-vue'
import FsDropdownMenu from './FsDropdownMenu'
import { becomeBezirk } from '@/script'
import Conference from './Conference'

export default {
  components: { BCollapse, FsDropdownMenu },
  directives: { VBToggle },
  mixins: [Conference],
  props: {
    regions: {
      type: Array,
      default: () => [],
    },
  },
  computed: {
    activeRegionId () {
      return ui.activeRegionId
    },
    activeRegion () {
      return this.regions.find(r => r.id === this.activeRegionId)
    },
    regionsSorted () {
      return this.regions.slice().sort((a, b) => {
        if (this.activeRegionId && a.id === this.activeRegionId) return -1
        if (this.activeRegionId && b.id === this.activeRegionId) return 1
        else return a.name.localeCompare(b.name)
      })
    },
  },
  methods: {
    joinRegionDialog () {
      this.$refs.dropdown.visible = false
      becomeBezirk()
    },
  },
}
</script>

<style lang="scss">
.regionMenu {
    margin-top: 0.1em;

    @media (max-width: 350px) {
        .dropdown-toggle::after {
            content: none;
        }
    }
}
.regionMenu > a.nav-link {
    font-family: 'Alfa Slab One',serif;
    font-size: 1em !important;
}
</style>
<style lang="scss" scoped>
.regionName {
    max-width: 110px;
    margin-bottom: -0.35em;
}
</style>
