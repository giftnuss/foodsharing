<template>
    <nav-item-dropdown tooltip="Nachrichten" no-caret right>
        <template slot="button-content">
            <i class="fa fa-comments"/>
            <span class="badge badge-danger">1</span>
        </template>
        blaaa...
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
            messages: []
        }
    }
}
</script>

<style>

</style>
