<template>
    <div id="topbar" class="bootstrap">
        <div class="navbar navbar-expand-md navbar-dark bg-primary ">
            <div v-if="!loggedIn" class="container">
                <div id="topbar-navleft">
                    <a :href="$url('home')" class="navbar-brand mr-4">food<span>shar<span>i</span>ng</span></a>
                    <login />
                </div>
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
                            Über uns
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
                            Infos
                        </template>
                        <a :href="$url('infos')" class="dropdown-item" role="menuitem">Infosammlung</a>
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
            </div>

            <div v-if="loggedIn" class="container">

                <div id="topbar-navleft">
                    <a class="navbar-brand" :href="$url('dashboard')">food<span>shar<span>i</span>ng</span></a>
                    <ul class="navbar-nav flex-row no-collapse">
                        
                        <menu-region v-if="hasFsRole" :regions="regions" :activeRegionId="241" />
                        <menu-stores v-if="hasFsRole && stores.length" :stores="stores" />
                        <menu-groups :workingGroups="workingGroups" />
                        <menu-baskets />
                        <li v-if="!isMobile" class="nav-item" v-b-tooltip title="Karte">
                            <a :href="$url('map')" class="nav-link">
                                <i class="fa fa-map-marker" />
                                <span v-if="!loggedIn">Karte</span>
                            </a>
                            
                        </li>
                        <menu-messages v-if="isMobile" />
                        <menu-bells v-if="isMobile" />
                    </ul>
                </div>              


                <search />
                <b-navbar-toggle target="nav_collapse"></b-navbar-toggle>
                
                <b-collapse is-nav id="nav_collapse">
                    <ul class="navbar-nav ml-auto">
                        <li class="nav-item" v-b-tooltip title="Home">
                            <a :href="$url('home')" class="nav-link">
                                <i class="fa fa-home" />
                                <span class="d-md-none">Startseite</span>
                            </a>
                        </li>
                        <li v-if="isMobile" class="nav-item" v-b-tooltip title="Karte">
                            <a :href="$url('map')" class="nav-link">
                                <i class="fa fa-map-marker" />
                                <span class="d-md-none">Karte</span>
                            </a>
                        </li>
                        <menu-admin 
                            v-if="someAdminRights"
                            :isOrgaTeam="isOrgaTeam"
                            :may="may"
                        />
                        <nav-item-dropdown tooltip="Informationen" right no-caret>
                            <template slot="button-content">
                                <i class="fa fa-info "/>
                                <span class="d-md-none">Infomationen</span>
                            </template>
                            <a :href="$url('vision')" class="dropdown-item" role="menuitem">Vision</a>
                            <a :href="$url('claims')" class="dropdown-item" role="menuitem">Forderungen</a>
                            <a :href="$url('partner')" class="dropdown-item" role="menuitem">Partner</a>
                            <a :href="$url('statistics')" class="dropdown-item" role="menuitem">Statistik</a>
                            <div class="dropdown-divider" />
                            <a :href="$url('infos')" class="dropdown-item" role="menuitem">Infosammlung</a>
                            <a :href="$url('blog')" class="dropdown-item" role="menuitem">Blog</a>
                            <a :href="$url('faq')" class="dropdown-item" role="menuitem">F.A.Q.</a>
                            <a :href="$url('guide')" class="dropdown-item" role="menuitem">Ratgeber</a>
                            <a :href="$url('wiki')" class="dropdown-item" role="menuitem">Wiki</a>
                        </nav-item-dropdown>
                        
                        <li v-if="mailbox" class="nav-item" v-b-tooltip title="E-Mail-Postfach">
                            <a :href="$url('mailbox')" class="nav-link">
                                <i class="fa fa-envelope" />
                                <span class="d-md-none">E-Mail-Postfach</span>
                            </a>
                        </li>

                        <menu-messages v-if="!isMobile" />
                        <menu-bells v-if="!isMobile" />
                        <menu-user :userId="fsId" :avatar="image" :isMobile="isMobile" />

                    </ul>
                </b-collapse>
            </div>
        </div>
    </div>
</template>


<script>
import ui from '@/stores/ui'
import bTooltip from '@b/directives/tooltip/tooltip'
import bCollapse from '@b/components/collapse/collapse'
import bNavbarToggle from '@b/components/navbar/navbar-toggle'


import NavItemDropdown from './NavItemDropdown'
import MenuRegion from './MenuRegion'
import MenuStores from './MenuStores'
import MenuGroups from './MenuGroups'
import MenuBaskets from './MenuBaskets'
import MenuAdmin from './MenuAdmin'
import MenuMessages from './MenuMessages'
import MenuBells from './MenuBells'
import MenuUser from './MenuUser'
import Search from './Search'
import Login from './Login'

export default {
    components: { bCollapse, bNavbarToggle, NavItemDropdown, MenuRegion, MenuStores, MenuGroups, MenuBaskets, MenuAdmin, MenuMessages, MenuBells, MenuUser, Search, Login},
    directives: { bTooltip },
    props: {
        fsId: {
            type: Number,
            default: null
        },
        loggedIn: {
            type: Boolean,
            default: true
        },
        image: {
            type: String,
            default: ''
        },
        mailbox: {
            type: Boolean,
            default: true
        },
        hasFsRole: {
            type: Boolean,
            default: true
        },
        isOrgaTeam: {
            type: Boolean,
            default: true
        },
        may: {
            type: Object,
            default: () => ({}) 
        },
        stores: {
            type: Array,
            default: () => []
        },
        regions: {
            type: Array,
            default: () => []
        },
        workingGroups: {
            type: Array,
            default: () => []
        }
    },
    computed: {
        someAdminRights() {
            return this.isOrgaTeam || this.may.editBlog || this.may.editQuiz || this.may.handleReports
        },
        isMobile() {
            return this.ui.wSM || this.ui.wXS
        },
        ui() {
            return ui
        }
    }
}
</script>

<style lang="scss" scoped>
#topbar {
    box-shadow: 0 0 5px rgba(0, 0, 0, 0.5);
    .container {
        max-width: 1000px;
    }


    // logo
    .navbar-brand {
        font-family: 'Alfa Slab One';
        color: #ffffff;
        margin-right: 0;
        font-size: 1.1rem;
        span {
            color: #64ae25;
        }
        span span {
            position: relative;
            &:hover::before {
                content: '♥';
                color: red;
                position: absolute;
                font-size: 0.5em;
                margin-top: -0.04em;
                margin-left: -0.085em;
            }
        }
    }
    @media (max-width: 700px) {
        .navbar-brand {
            font-size: 0.4rem;
        }
    }

    @media (max-width: 630px) {
        #topbar-navleft {
            width: 100%;
        }
    }

    .navbar-nav {
        align-items: center;
    }
    .navbar-collapse.collapse, .navbar-collapse.collapsing {
        .navbar-nav {
            align-items: start;
        }
    }
}
#topbar-navleft {
    display:flex;
    align-items: center;
    flex-grow: 1;
    margin-right: 1em;
}
</style>

<style lang="scss">
#topbar {
    .nav-link {
        padding: 0.4em 0.5em;
        i {
            font-size: 1.25em;
        }
    }
    @media (max-width: 700px) {
        .nav-link {
            padding: 0.4em 0.2em;
            i {
                font-size: 1em;   
            }
        }
    }
    .no-collapse {
        display:flex;
        flex-grow: 1;
        .nav-link {
            padding-right: 0.5rem;
            padding-left: 0.5rem;
        }
        .dropdown-menu {
            position: absolute;
        }
    }
    .nav-item > a > .badge {
        position: absolute;
        margin-top: -0.5em;
        margin-left: -0.7em;
    }
    ul {
        margin-left: 0;
    }

    // dropdown styles
    .dropdown-item i {
        display: inline-block;
        width: 1.7em;
        text-align: center;
        margin-left: -0.4em;
    }
    .dropdown-menu .sub .dropdown-item {
        font-size: 0.8em;
        padding-left: 3em;
        font-weight: normal;
    }
     .dropdown-item.sub {
        padding-left: 2.5em;

     }
    .dropdown-item {
        font-weight: bold;
        font-size: 0.9em;
    }

    .dropdown-menu {
        max-height: 350px;
        max-width: 300px;
        overflow-y: auto;
    }

    @media (max-width: 500px) {
        .dropdown {
            position: initial;
        }
        .dropdown-menu {
            width: 100%;
            max-width: initial;
            top: 2.2em;
        }
        #search-results {
            top: 5em;
            width: 100%;
            left: 0px !important;
        }
    }
    .navbar-collapse.collapsing, .navbar-collapse.show {
        .nav-link i {
            width: 40px;
            text-align: center;
        }
        li {
            width: 100%;
        }
    }
}

</style>

