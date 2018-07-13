<template>
    <div>
        <div v-if="isMobile" class="nav-item b-nav-dropdown dropdown">
            <a href="/?page=listFaq" role="menuitem" class="nav-link"><i class="fa fa-address-card" /> Profil</a>
            <a href="/?page=listFaq" role="menuitem" class="nav-link"><i class="fa fa-cog" /> Einstellungen</a>
            <a href="/?page=listFaq" role="menuitem" class="nav-link"><i class="fa fa-power-off" /> Logout</a>
        </div>
        <nav-item-dropdown v-else no-caret class="user" right>
            <template slot="button-content">
                <img :src="avatar" />
            </template>
            <a href="/?page=listFaq" role="menuitem" class="dropdown-item"><i class="fa fa-address-card" /> Profil</a>
            <a href="/?page=listFaq" role="menuitem" class="dropdown-item"><i class="fa fa-cog" /> Einstellungen</a>
            <a href="/?page=listFaq" role="menuitem" class="dropdown-item"><i class="fa fa-power-off" /> Logout</a>
        </nav-item-dropdown>
    </div>
</template>
<script>
import NavItemDropdown from './NavItemDropdown'
export default {
    components: {
        NavItemDropdown
    },
    props: {
        avatar: {
            type: String
        },
        isMobile: {
            type: Boolean
        }
    }
}
</script>

<style>

</style>
