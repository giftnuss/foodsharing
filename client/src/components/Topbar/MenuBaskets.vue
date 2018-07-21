<template>
    <nav-item-dropdown tooltip="Essenskörbe" :no-caret="!showLabel" class="topbar-baskets" ref="dropdown">
        <template slot="button-content">
            <i class="fa fa-shopping-basket"/>
            <span v-if="showLabel">Essenskörbe</span>
        </template>
        <div class="list-group">
            <p v-if="!baskets.length" class="dropdown-header">
                Du hast keine Essenskörbe eingetragen
            </p>
            <div class="scroll-container">
                <menu-baskets-entry
                    v-for="basket in basketsSorted"
                    :key="basket.id"
                    :basket="basket"
                    @basketRemove="openRemoveBasketForm"
                />
            </div>
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
import basketStore from '@/stores/baskets'

import { ajreq } from '@/script'

export default {
    components: {
        NavItemDropdown,
        MenuBasketsEntry
    },
    props: {
        showLabel: {
            type: Boolean
        }
    },
    created() {
        basketStore.loadBaskets()
    },
    methods: {
        openBasketCreationForm() {
            this.$refs.dropdown.visible = false
            ajreq('newbasket', {app:'basket'})
        },
        openRemoveBasketForm(basketId, userId) {
            this.$refs.dropdown.visible = false
            ajreq('removeRequest', {
                app:'basket',
                id: basketId,
                fid: userId
            })
        }
    },
    computed: {
        baskets() {
            return basketStore.baskets
        },
        basketsSorted() {
            return this.baskets.sort( (a,b) => b.updatedAt.localeCompare(a.updatedAt))
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
  