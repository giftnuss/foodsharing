<template>
    <nav-item-dropdown tooltip="Administration" right no-caret>
        <template slot="button-content">
            <i class="fa fa-gear"/>
            <span class="d-md-none">Administration</span>
        </template>
        <!-- TODO: replace hard coded links with $url() -->
        <a v-if="isOrgaTeam" class="dropdown-item" href="/?page=betrieb&bid=0">{{ $i18n('menu_all_store') }}</a>
        <a v-if="isOrgaTeam" class="dropdown-item" href="/?page=foodsaver&bid=0">{{ $i18n('menu_all_fs') }}</a>
        <a v-if="isOrgaTeam" class="dropdown-item" href="/?page=geoclean&sub=lostregion">{{ $i18n('menu_regions_without_bots') }}</a>
        <a v-if="isOrgaTeam" class="dropdown-item" href="/?page=region">{{ $i18n('menu_manage_regions') }}</a>
        <a v-if="isOrgaTeam" class="dropdown-item" href="/?page=newarea">{{ $i18n('menu_newarea') }}</a>
        <a v-if="may.editBlog" class="dropdown-item" href="/?page=blog&sub=manage">{{ $i18n('menu_blog') }}</a>
        <a v-if="isOrgaTeam" class="dropdown-item" href="/?page=email">{{ $i18n('menu_email') }}</a>
        <a v-if="isOrgaTeam" class="dropdown-item" href="/?page=message_tpl">{{ $i18n('menu_email_tpl') }}</a>
        <a v-if="isOrgaTeam" class="dropdown-item" href="/?page=faq">{{ $i18n('menu_faq') }}</a>
        <a v-if="isOrgaTeam" class="dropdown-item" href="/?page=geoclean">{{ $i18n('menu_foodsaver_without_region') }}</a>
        <a v-if="isOrgaTeam" class="dropdown-item" href="/?page=content">{{ $i18n('menu_content') }}</a>
        <a v-if="isOrgaTeam" class="dropdown-item" href="/?page=lebensmittel">{{ $i18n('menu_foodtypes') }}</a>
        <a v-if="isOrgaTeam" class="dropdown-item" href="/?page=mailbox&a=manage">{{ $i18n('menu_mailbox_manage') }}</a>
        <a v-if="may.editQuiz" class="dropdown-item" href="/?page=quiz">{{ $i18n('menu_quiz') }}</a>
        <a v-if="may.handleReports" class="dropdown-item" href="/?page=report&sub=uncom">{{ $i18n('menu_reports') }}</a>
    </nav-item-dropdown>
</template>
<script>
import NavItemDropdown from './NavItemDropdown'
export default {
    components: {
        NavItemDropdown
    },
    props: {
        isOrgaTeam: {
            type: Boolean,
        },
        may: {
            type: Object
        }
    }
}
</script>

<style>

</style>
