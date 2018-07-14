<template>
    <div id="topbar" class="bootstrap">
        <div class="navbar navbar-expand-md navbar-dark bg-primary ">
            <div class="container">

                <div id="topbar-navleft">
                    <a class="navbar-brand" href="#">food<span>shar<span>i</span>ng</span></a>
                    <ul class="navbar-nav flex-row no-collapse">
                        
                        <menu-region v-if="hasFsRole" :regions="regions" :activeRegionId="241" />
                        <menu-stores v-if="hasFsRole && stores.length" :stores="stores" />
                        <menu-groups :workingGroups="workingGroups" />
                        <menu-baskets />
                        <li v-if="!isMobile" class="nav-item" v-b-tooltip title="Karte">
                            <a href="#" class="nav-link"><i class="fa fa-map-marker" /></a>
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
                            <a href="#" class="nav-link">
                                <i class="fa fa-home" />
                                <span class="d-md-none">Startseite</span>
                            </a>
                        </li>
                        <li v-if="isMobile" class="nav-item" v-b-tooltip title="Karte">
                            <a href="#" class="nav-link">
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
                            <a class="dropdown-item" href="/news" role="menuitem">News</a>
                            <a class="dropdown-item" href="/ueber-uns" role="menuitem">Über uns</a>
                            <a class="dropdown-item" href="/?page=listFaq" role="menuitem">F.A.Q.</a>
                            <a class="dropdown-item" href="https://wiki.foodsharing.de/" role="menuitem">Wiki</a>
                            <a class="dropdown-item" href="/ratgeber" role="menuitem">Ratgeber</a>
                            <a class="dropdown-item" href="/unterstuetzung" role="menuitem">Spendenaufruf</a>
                            <a class="dropdown-item" href="/statistik" role="menuitem">Statistik</a>
                            <a class="dropdown-item" href="/?page=content&sub=changelog" role="menuitem">Changelog</a>
                        </nav-item-dropdown>
                        <li v-if="mailbox" class="nav-item" v-b-tooltip title="E-Mail-Postfach">
                            <a href="#" class="nav-link">
                                <i class="fa fa-envelope" />
                                <span class="d-md-none">E-Mail-Postfach</span>
                            </a>
                        </li>
                        <menu-messages v-if="!isMobile" />
                        <menu-bells v-if="!isMobile" />
                        <menu-user :avatar="image" :isMobile="isMobile" />

                    </ul>
                </b-collapse>
            </div>
        </div>
    </div>
</template>


<script>

// TODO: remove complete bootstrap-vue import again
import BootstrapVue from 'bootstrap-vue'
import Vue from 'vue'
Vue.use(BootstrapVue);



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

export default {
    components: {NavItemDropdown, MenuRegion, MenuStores, MenuGroups, MenuBaskets, MenuAdmin, MenuMessages, MenuBells, MenuUser, Search},
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
            return false
        }
    }
}
</script>

<style lang="scss">
#topbar {
    box-shadow: 0 0 5px rgba(0, 0, 0, 0.5);

    .container {
        max-width: 1000px;
    }
    .navbar-brand {
        font-family: 'Alfa Slab One';
        color: #ffffff;
        margin-right: 0;
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
        font-size: 1.1rem;
    }
    @media (max-width: 700px) {
        .navbar-brand {
            font-size: 0.4rem;
            margin-top: .5rem;
        }
    }
    .dropdown-item i {
        display: inline-block;
        width: 1.7em;
        text-align: center;
        margin-left: -0.4em;
    }
    .dropdown-menu .sub .dropdown-item {
        font-size: 0.9em;
        padding-left: 3em;
        font-weight: normal;

    }
    .dropdown-item {
        font-size: 14px;
        font-weight: bold;
    }
    .user {
        & > a {
            padding: 0 0.5rem !important;
        }
        img {
            height: 2.4em;
        }
    }
    .dropdown-menu {
        max-height: 400px;
        overflow-y: auto;
    }
}
#topbar-navleft {

    display:flex;
    flex-grow: 1;

    & > ul {
        margin-left: 0;
    }
    .dropdown-menu {
        position: absolute;
    }
    .nav-link {
        padding-right: 0.5rem;
        padding-left: 0.5rem;
    }
}

.nav-item > a > .badge {
    position: absolute;
    margin-top: -0.5em;
    margin-left: -0.7em;
}
.navbar-collapse.collapsing, .navbar-collapse.show {
    .nav-link i {
        width: 40px;
        text-align: center;
    }
}
</style>

