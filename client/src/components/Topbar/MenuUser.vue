<template>
    <nav-item-dropdown tooltip="" no-caret class="user" right>
        <template slot="button-content">
            <img src="https://foodsharing.de/images/130_q_88b5cfe99204203861b289a0ff8827ce.jpg" />
        </template>
        <a href="/?page=listFaq" role="menuitem" class="dropdown-item"><i class="fa fa-address-card" /> Profil</a>
        <a href="/?page=listFaq" role="menuitem" class="dropdown-item"><i class="fa fa-cog" /> Einstellungen</a>
        <a href="/?page=listFaq" role="menuitem" class="dropdown-item"><i class="fa fa-power-off" /> Logout</a>
    </nav-item-dropdown>
</template>
<script>
import NavItemDropdown from './NavItemDropdown'
export default {
    components: {
        NavItemDropdown
    },
    data() {
        return {
            bells: []
        }
    }
}
</script>

<style>

</style>
