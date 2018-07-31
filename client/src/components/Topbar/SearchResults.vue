<template>
    <div>
        <div v-if="isEmpty" class="dropdown-header alert alert-warning">
            Es konnten keine Ergebnisse gefunden werden
        </div>
        <div v-if="filtered.myGroups.length">
            <h3 class="dropdown-header"><i class="fa fa-users" /> Meine Gruppen</h3>
            <search-result-entry v-for="group in filtered.myGroups" 
                :key="group.id" 
                :href="$url('forum', group.id)" 
                :title="group.name" 
                :teaser="group.teaser"
                :image="group.image"
            />
            <div class="dropdown-divider"></div>
        </div>
        <div v-if="filtered.myStores.length">
            <h3 class="dropdown-header"><i class="fa fa-shopping-cart" /> Meine Betriebe</h3>
            <search-result-entry v-for="store in filtered.myStores" 
                :key="store.id" 
                :href="$url('store', store.id)"  
                :title="store.name" 
                :teaser="store.teaser"
                :image="store.image"
            />
            <div class="dropdown-divider"></div>
        </div>
        <div v-if="filtered.myRegions.length">
            <h3 class="dropdown-header"><i class="fa fa-home" /> Meine Bezirke</h3>
            <search-result-entry v-for="region in filtered.myRegions" 
                :key="region.id" 
                :href="$url('forum', region.id)" 
                :title="region.name" 
                :teaser="region.teaser"
                :image="region.image"
            />
            <div class="dropdown-divider"></div>
        </div>


        <div v-if="filtered.groups.length">
            <h3 class="dropdown-header"><i class="fa fa-users" /> Gruppen</h3>
            <search-result-entry v-for="group in filtered.groups" 
                :key="group.id" 
                :href="$url('forum', group.id)" 
                :title="group.name" 
                :teaser="group.teaser"
                :image="group.image"
            />
            <div class="dropdown-divider"></div>
        </div>
        <div v-if="filtered.users.length">
            <h3 class="dropdown-header"><i class="fa fa-child" /> Foodsaver</h3>
            <search-result-entry v-for="user in filtered.users" 
                :key="user.id" 
                :href="$url('profile', user.id)" 
                :title="user.name" 
                :teaser="user.teaser"
                :image="user.image"
            />
            <div class="dropdown-divider"></div>
        </div>
        <div v-if="filtered.stores.length">
            <h3 class="dropdown-header"><i class="fa fa-shopping-cart" /> Betriebe</h3>
            <search-result-entry v-for="store in filtered.stores" 
                :key="store.id" 
                :href="$url('store', store.id)" 
                :title="store.name" 
                :teaser="store.teaser"
                :image="store.image"
            />
            <div class="dropdown-divider"></div>
        </div>
        <div v-if="filtered.regions.length">
            <h3 class="dropdown-header"><i class="fa fa-home" /> Bezirke</h3>
            <search-result-entry v-for="region in filtered.regions" 
                :key="region.id" 
                :href="$url('forum', region.id)" 
                :title="region.name" 
                :teaser="region.teaser"
                :image="region.image"
            />
        </div>
        
    </div>
</template>


<script>
import SearchResultEntry from './SearchResultEntry'

function arrayFilterDuplicate(list, ignore) {
    let ids = ignore.map(e => e.id)
    return list.filter(e => ids.indexOf(e.id) == -1)
}
export default {
    components: { SearchResultEntry },
    props: {
        stores: {
            type: Array,
            default: () => []
        },
        groups: {
            type: Array,
            default: () => []
        },
        regions: {
            type: Array,
            default: () => []
        },
        users: {
            type: Array,
            default: () => []
        },
        myGroups: {
            type: Array,
            default: () => []
        },
        myStores: {
            type: Array,
            default: () => []
        },
        myRegions: {
            type: Array,
            default: () => []
        },
        query: {
            type: String,
            default: ''
        },
    },
    computed: {
        filtered() {
            let query = this.query.toLowerCase().trim()

            // filter elements, whether the query string is contained in name or teaser
            let filterFunction = (e) => {
                if(!query) return false
                if(e.name && e.name.toLowerCase().indexOf(query) !== -1) return true
                if(e.teaser && e.teaser.toLowerCase().indexOf(query) !== -1) return true
                return false
            }
            let res = {
                stores: this.stores.filter(filterFunction),
                regions: this.regions.filter(filterFunction),
                users: this.users.filter(filterFunction),
                groups: this.groups.filter(filterFunction),
                myGroups: this.myGroups.filter(filterFunction),
                myStores: this.myStores.filter(filterFunction),
                myRegions: this.myRegions.filter(filterFunction)
            }

            // additionally remove elements in gobal search wich are already contained in the private lists

            res.stores = arrayFilterDuplicate(res.stores, res.myStores)
            res.groups = arrayFilterDuplicate(res.groups, res.myGroups)
            res.regions = arrayFilterDuplicate(res.regions, res.myRegions)
            
            // because myGroups are still contained in the regions reponse, we filter them out additionally
            res.regions = arrayFilterDuplicate(res.regions, res.myGroups)
            return res
        },
        isEmpty() {
            return (
                !this.filtered.stores.length &&
                !this.filtered.regions.length &&
                !this.filtered.users.length &&
                !this.filtered.groups.length &&
                !this.filtered.myGroups.length &&
                !this.filtered.myStores.length &&
                !this.filtered.myRegions.length
            )
        }
    }
}
</script>

<style lang="scss" scoped>
.dropdown-header {
    white-space: normal;
    margin-bottom: 0;
}
</style>
