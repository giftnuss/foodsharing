<template>
    <nav-item-dropdown tooltip="Essenskörbe" no-caret>
        <template slot="button-content">
            <i class="fa fa-shopping-basket"/>
        </template>
        <div v-for="store in stores" :key="store.id">
            <a role="menuitem" :href="$url('events', store.id)"  class="dropdown-item">{{ store.name }} </a>
        </div>
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
