<template>
    <nav-item-dropdown tooltip="Deine Gruppen" no-caret>
        <template slot="button-content">
            <i class="fa fa-users"/>
        </template>
            <a :href="$url('workingGroups')" role="menuitem" class="dropdown-item"><i class="fa fa-users" /> Gruppenübersicht</a>
            <div v-for="group in workingGroups" :key="group.id">
                <a v-if="!alwaysOpen" role="menuitem" v-b-toggle="'topbargroup_'+group.id"  class="dropdown-item">{{ group.name }}</a>
                <h3 v-if="alwaysOpen" role="menuitem" class="dropdown-header">{{ group.name }}</h3>
                <b-collapse class="sub" :visible="alwaysOpen" :id="'topbargroup_'+group.id" :accordion="alwaysOpen ? null : 'groups'">
                    <a role="menuitem" :href="$url('forum', group.id)" class="dropdown-item"><i class="fa fa-comment-o" /> Forum</a>
                    <a role="menuitem" :href="$url('events', group.id)" class="dropdown-item"><i class="fa fa-calendar" /> Termine</a>
                    <a role="menuitem" :href="$url('wall', group.id)" class="dropdown-item"><i class="fa fa-group" /> Pinnwand</a>
                    <a role="menuitem" :href="$url('workingGroupEdit', group.id)" class="dropdown-item"><i class="fa fa-cog" /> Gruppe verwalten</a>
                </b-collapse>
            </div>
    </nav-item-dropdown>
</template>
<script>
import bCollapse from '@b/components/collapse/collapse';
import bToggle from '@b/directives/toggle/toggle';
import NavItemDropdown from './NavItemDropdown'

export default {
    components: { bCollapse, NavItemDropdown },
    directives: { bToggle },
    computed: {
        alwaysOpen() {
            return this.workingGroups.length <= 2
        }
    },
    props: {
        workingGroups: {
            type: Array,
            default: () => []
        },
    }
}
</script>

<style>

</style>
 