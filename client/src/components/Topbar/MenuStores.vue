<template>
    <nav-item-dropdown tooltip="Deine Betriebe" no-caret>
        <template slot="button-content">
            <i class="fa fa-shopping-cart"/>
        </template>
        <div v-for="store in stores" :key="store.id">
            <a role="menuitem" :href="$url('events', store.id)"  class="dropdown-item"><i class="fa fa-shopping-cart"/> {{ store.name }} </a>
        </div>
        <div v-if="stores.length" class="dropdown-divider"></div>
        <a :href="$url('storeAdd')" role="menuitem" class="dropdown-item"><small><i class="fa fa-plus" /> Einem Betrieb hinzufügen</small></a>
    </nav-item-dropdown>
</template>
<script>
import NavItemDropdown from './NavItemDropdown'
export default {
    components: {
        NavItemDropdown
    },
    props: {
        stores: {
            type: Array,
            default: () => []
        },
    }
}
</script>

<style>

</style>
