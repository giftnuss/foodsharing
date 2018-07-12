<template>
    <nav-item-dropdown tooltip="Benachrichtigungen" no-caret right>
        <template slot="button-content">
            <i class="fa fa-bell"/>
            <span class="badge badge-danger">4</span>
        </template>
        heyho
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
