<template>
    <nav-item-dropdown tooltip="Administration" right no-caret>
        <template slot="button-content">
            <i class="fa fa-gear"/>
            <span class="d-md-none">Administration</span>
        </template>
        <a v-for="item in items" :key="item.url" class="dropdown-item" :href="item.url">
            <i :class="item.icon" /> {{ item.label }}
        </a>
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
    },
    computed: {
        items() {
            // TODO: replace hard coded links with $url()
            let items = []
            if(this.isOrgaTeam) {
                items.push(...[
                    {
                        url: '/?page=region',
                        icon: 'fa fa-map',
                        label: this.$i18n('menu_manage_regions')
                    },
                    {
                        url: '/?page=geoclean&sub=lostregion',
                        icon: 'fa fa-map',
                        label: this.$i18n('menu_regions_without_bots')
                    },
                    {
                        url: '/?page=newarea',
                        icon: 'fa fa-map-o',
                        label: this.$i18n('menu_newarea')
                    },
                    {
                        url: '/?page=email',
                        icon: 'fa fa-envelope',
                        label: this.$i18n('menu_email')
                    },
                    {
                        url: '/?page=message_tpl',
                        icon: 'fa fa-envelope',
                        label: this.$i18n('menu_email_tpl')
                    },
                    {
                        url: '/?page=faq',
                        icon: 'fa fa-question',
                        label: this.$i18n('menu_faq')
                    },
                    {
                        url: '/?page=geoclean',
                        icon: 'fa fa-user',
                        label: this.$i18n('menu_foodsaver_without_region')
                    },
                    {
                        url: '/?page=mailbox&a=manage',
                        icon: 'fa fa-envelope-o',
                        label: this.$i18n('menu_mailbox_manage')
                    },
                    {
                        url: '/?page=content',
                        icon: 'fa fa-file-text',
                        label: this.$i18n('menu_content')
                    }
                ])
            }
            if(this.may.editBlog) {
                items.push({
                    url: '/?page=blog&sub=manage',
                    icon: 'fa fa-newspaper-o',
                    label: this.$i18n('menu_blog')
                })
            }
            if(this.may.editQuiz) {
                items.push({
                    url: '/?page=quiz',
                    icon: 'fa fa-question-circle',
                    label: this.$i18n('menu_quiz')
                })
            }
            if(this.may.handleReports) {
                items.push(    {
                    url: '/?page=report&sub=uncom',
                    icon: 'fa fa-exclamation',
                    label: this.$i18n('menu_reports')
                })
            }

            return items.sort((a,b) => a.label.localeCompare(b.label))
        }
    }
}
</script>

<style>

</style>
