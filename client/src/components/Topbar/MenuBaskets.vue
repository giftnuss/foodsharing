<template>
    <nav-item-dropdown tooltip="Essenskörbe" no-caret class="topbar-baskets" ref="dropdown">
        <template slot="button-content">
            <i class="fa fa-shopping-basket"/>
        </template>
        <div class="list-group">
            <p v-if="!baskets.length" class="dropdown-header">
                Du hast keine Essenskörbe eingetragen
            </p>
            <menu-baskets-entry
                v-for="basket in basketsSorted"
                :key="basket.id"
                :basket="basket"
            />
            <div class="list-grou-item p-2 text-center">
                <a :href="$url('baskets')" class="btn btn-sm btn-secondary">
                    Alle Essenskörbe
                </a>
                <a href="#" @click="openBasketCreationForm" class="btn btn-sm btn-secondary">
                    Essenskorb anlegen
                </a>
            </div>
        </div>
    </nav-item-dropdown>
</template>
<script>
import NavItemDropdown from './NavItemDropdown'
import MenuBasketsEntry from './MenuBasketsEntry'
import { getBaskets } from '@/api/baskets'
import { ajreq } from '@/script'

function stringCompare(str1, str2) {
    return str1 < str2 ? -1 : str1 > str2
}

export default {
    components: {
        NavItemDropdown,
        MenuBasketsEntry
    },
    data() {
        return {
            baskets: []
        }
    },
    created() {
        this.loadBaskets()
    },
    methods: {
        async loadBaskets() {
            this.baskets = await getBaskets()
        },
        openBasketCreationForm() {
            this.$refs.dropdown.visible = false
            ajreq('newbasket', {app:'basket'})
        }
    },
    computed: {
        basketsSorted() {
            return this.baskets.sort( (a,b) => stringCompare(b.updatedAt, a.updatedAt))
        }
    }
}
</script>

<style lang="scss">
.topbar-baskets {
    .dropdown-menu {
        overflow-x: hidden;
        padding: 0;
    }
}
</style>
  