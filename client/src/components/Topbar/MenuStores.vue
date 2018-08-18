<template>
    <nav-item-dropdown tooltip="Deine Betriebe" no-caret>
        <template slot="button-content">
            <i class="fas fa-shopping-cart"/>
        </template>
        <div v-for="store in stores" :key="store.id">
            <a role="menuitem" :href="$url('store', store.id)"  class="dropdown-item text-truncate"> {{ store.name }} </a>
        </div>
        <div v-if="stores.length && mayAddStore" class="dropdown-divider"></div>
        <a v-if="mayAddStore" :href="$url('storeAdd')" role="menuitem" class="dropdown-item"><small><i class="fas fa-plus" /> Einem Betrieb hinzufügen</small></a>
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
        mayAddStore: {
            type: Boolean
        }
    }
}
</script>

<style>

</style>
