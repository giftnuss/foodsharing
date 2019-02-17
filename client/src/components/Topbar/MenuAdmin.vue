<template>
  <nav-item-dropdown
    tooltip="Administration"
    right
    no-caret
  >
    <template slot="button-content">
      <i class="fas fa-cog" />
      <span class="d-md-none">
        Administration
      </span>
    </template>
    <a
      v-for="item in items"
      :key="item.url"
      :href="item.url"
      class="dropdown-item"
    >
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
      default: false
    },
    may: {
      type: Object,
      default: () => {}
    }
  },
  computed: {
    items () {
      // TODO: replace hard coded links with $url()
      let items = []
      if (this.isOrgaTeam) {
        items.push(...[
          {
            url: '/?page=region',
            icon: 'fas fa-map',
            label: this.$i18n('menu_manage_regions')
          },
          {
            url: '/?page=geoclean&sub=lostregion',
            icon: 'fas fa-map',
            label: this.$i18n('menu_regions_without_bots')
          },
          {
            url: '/?page=email',
            icon: 'fas fa-envelope',
            label: this.$i18n('menu_email')
          },
          {
            url: '/?page=message_tpl',
            icon: 'fas fa-envelope',
            label: this.$i18n('menu_email_tpl')
          },
          {
            url: '/?page=faq',
            icon: 'fas fa-question',
            label: this.$i18n('menu_faq')
          },
          {
            url: '/?page=geoclean',
            icon: 'fas fa-user',
            label: this.$i18n('menu_foodsaver_without_region')
          },
          {
            url: '/?page=mailbox&a=manage',
            icon: 'far fa-envelope',
            label: this.$i18n('menu_mailbox_manage')
          },
          {
            url: '/?page=content',
            icon: 'fas fa-file-alt',
            label: this.$i18n('menu_content')
          }
        ])
      }
      if (this.may.editBlog) {
        items.push({
          url: '/?page=blog&sub=manage',
          icon: 'far fa-newspaper',
          label: this.$i18n('menu_blog')
        })
      }
      if (this.may.editQuiz) {
        items.push({
          url: '/?page=quiz',
          icon: 'fas fa-question-circle',
          label: this.$i18n('menu_quiz')
        })
      }
      if (this.may.handleReports) {
        items.push({
          url: '/?page=report&sub=uncom',
          icon: 'fas fa-exclamation',
          label: this.$i18n('menu_reports')
        })
      }

      return items.sort((a, b) => a.label.localeCompare(b.label))
    }
  }
}
</script>

<style>

</style>
