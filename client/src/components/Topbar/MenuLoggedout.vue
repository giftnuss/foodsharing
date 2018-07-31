<template>
    <ul class="navbar-nav ml-auto no-collapse" id="topbar-navright">
        <li class="nav-item">
            <a :href="$url('map')" class="nav-link">
                <i class="fa fa-map-marker" />
            </a>
        </li>
        <li class="nav-item">
            <a :href="$url('joininfo')" class="nav-link">
                <i class="fa fa-rocket" />
                Mach mit!
            </a>
        </li>

        <nav-item-dropdown rightt>
            <template slot="button-content">
                <i class="fa fa-bullhorn "/>
                <span v-if="!wXS">Über uns</span>
            </template>
            <a :href="$url('vision')" class="dropdown-item" role="menuitem">Vision</a>
            <a :href="$url('claims')" class="dropdown-item" role="menuitem">Forderungen</a>
            <a :href="$url('partner')" class="dropdown-item" role="menuitem">Partner</a>
            <a :href="$url('statistics')" class="dropdown-item" role="menuitem">Statistik</a>
            <a :href="$url('infosCompany')" class="dropdown-item" role="menuitem">Für Unternehmen</a>
        </nav-item-dropdown>

        <nav-item-dropdown right>
            <template slot="button-content">
                <i class="fa fa-info "/>
                <span class="d-none d-lg-inline">Infos</span>
            </template>
            <a :href="$url('infos')" class="dropdown-item" role="menuitem">Infosammlung</a>
            <a :href="$url('donate')" class="dropdown-item" role="menuitem">Spenden</a>
            <a :href="$url('blog')" class="dropdown-item" role="menuitem">Blog</a>
            <a :href="$url('faq')" class="dropdown-item" role="menuitem">F.A.Q.</a>
            <a :href="$url('guide')" class="dropdown-item" role="menuitem">Ratgeber</a>
            <a :href="$url('wiki')" class="dropdown-item" role="menuitem">Wiki</a>
        </nav-item-dropdown>

        <nav-item-dropdown tooltip="Kontakt" right>
            <template slot="button-content">
                <i class="fa fa-envelope" />
            </template>
            <h3 class="dropdown-header">Communities</h3>
            <a :href="$url('communitiesGermany')" class="dropdown-item sub" role="menuitem">Deutschland</a>
            <a :href="$url('communitiesAustria')" class="dropdown-item sub" role="menuitem">Österreich</a>
            <a :href="$url('communitiesSwitzerland')" class="dropdown-item sub" role="menuitem">Schweiz</a>
            <div class="dropdown-divider" />
            <a :href="$url('team')" class="dropdown-item" role="menuitem">Team</a>
            <a :href="$url('press')" class="dropdown-item" role="menuitem">Presse</a>
            <a :href="$url('imprint')" class="dropdown-item" role="menuitem">Impressum</a>
        </nav-item-dropdown>
    </ul>
</template>

<script>
import NavItemDropdown from './NavItemDropdown'

export default {
    components: { NavItemDropdown },
    props: {
        wXS: {
            type: Boolean
        },
        wSM: {
            type: Boolean
        },
    }
}
</script>
