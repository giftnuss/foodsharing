<template>
    <b-nav-item-dropdown class="regionMenu" >
            <template slot="button-content">
                {{ activeRegion ? activeRegion.name : 'Bezirke' }}
            </template>
            <div v-for="region in regionsSorted" :key="region.id">
                <a v-if="region.id !== activeRegionId || regions.length !== 1" role="menuitem" v-b-toggle="'topbarregion_'+region.id" href="#" target="_self" class="dropdown-item">{{ region.name }}</a>
                <b-collapse class="sub" :id="'topbarregion_'+region.id" accordion="regions" :visible="region.id === activeRegionId">
                    <a role="menuitem" :href="$url('forum', region.id)" class="dropdown-item dropdown-item-sub"><i class="fa fa-comment-o" />Forum</a>
                    <a v-if="region.isBot" role="menuitem" :href="$url('forum', region.id, 1)" class="dropdown-item dropdown-item-sub"><i class="fa fa-commenting-o" />Bot-Forum</a>
                    <a role="menuitem" :href="$url('fairteiler', region.id)" class="dropdown-item dropdown-item-sub"><i class="fa fa-recycle" />Fair-Teiler</a>
                    <a role="menuitem" :href="$url('events', region.id)"  class="dropdown-item dropdown-item-sub"><i class="fa fa-calendar" />Termine</a>
                    <a role="menuitem" :href="$url('stores', region.id)"  class="dropdown-item dropdown-item-sub"><i class="fa fa-cart-plus" />Betriebe</a>
                    <a role="menuitem" :href="$url('workingGroups', region.id)"  class="dropdown-item dropdown-item-sub"><i class="fa fa-group" />Arbeitsgruppen</a>
                    <a v-if="region.isBot" role="menuitem" :href="$url('foodsaverList', region.id)"  class="dropdown-item dropdown-item-sub"><i class="fa fa-user" />Foodsaver</a>
                    <a v-if="region.isBot" role="menuitem" :href="$url('passports', region.id)"  class="dropdown-item dropdown-item-sub"><i class="fa fa-address-card" />Ausweise</a>
                </b-collapse>
            </div>
            <div v-if="regionsSorted.length" class="dropdown-divider"></div>
            <a href="#" role="menuitem" class="dropdown-item"><small><i class="fa fa-plus" /> Einem Bezirk beitreten</small></a>
        </b-nav-item-dropdown>
</template>
<script>
export default {
    props: {
        regions: {
            type: Array,
            default: () => []
        },
        activeRegionId: {}
    },
    computed: {
        activeRegion() {
            return this.regions.find(r => r.id === this.activeRegionId)
        },
        regionsSorted() {
            return this.regions.sort((a,b) => {
                if(this.activeRegionId && a.id == this.activeRegionId) return -1
                if(this.activeRegionId && b.id == this.activeRegionId) return 1
                else return a.name-b.name
            })
        }
    }
}
</script>

<style>
.regionMenu > a {
    font-family: 'Alfa Slab One';
    /* margin-top: -35px; */

}
</style>
