<template>
  <fs-dropdown-menu
    id="dropdown-admin"
    ref="dropdown"
    menu-title="menu.entry.administration"
    right
    icon="fa-cog"
  >
    <b-dropdown-item
      v-for="item in items"
      :key="item.url"
      :href="item.url"
    >
      <i :class="item.icon" /> {{ item.label }}
    </b-dropdown-item>
  </fs-dropdown-menu>
</template>

<script>
import { url } from '@/urls'
import i18n from '@/i18n'
import FsDropdownMenu from './FsDropdownMenu'

export default {
  components: { FsDropdownMenu },
  props: {
    may: {
      type: Object,
      default: () => {},
    },
  },
  computed: {
    items () {
      // TODO: replace hard coded links with $url()
      const items = []
      if (this.may.administrateBlog) {
        items.push({
          url: url('blogEdit'),
          icon: 'far fa-newspaper',
          label: i18n('menu.blog'),
        })
      }
      if (this.may.editQuiz) {
        items.push({
          url: url('quizEdit'),
          icon: 'fas fa-question-circle',
          label: i18n('menu.quiz'),
        })
      }
      if (this.may.handleReports) {
        items.push({
          url: url('reports'),
          icon: 'fas fa-exclamation',
          label: i18n('menu.reports'),
        })
      }
      if (this.may.administrateRegions) {
        items.push({
          url: url('region'),
          icon: 'fas fa-map',
          label: i18n('menu.manage_regions'),
        })
      }
      if (this.may.administrateNewsletterEmail) {
        items.push({
          url: url('email'),
          icon: 'fas fa-envelope',
          label: i18n('menu.email'),
        })
      }
      if (this.may.manageMailboxes) {
        items.push({
          url: url('mailboxManage'),
          icon: 'far fa-envelope',
          label: i18n('menu.manage_mailboxes'),
        })
      }
      if (this.may.editContent) {
        items.push({
          url: url('contentEdit'),
          icon: 'fas fa-file-alt',
          label: i18n('menu.content'),
        })
      }

      return items.sort((a, b) => a.label.localeCompare(b.label))
    },
  },
}
</script>

<style>
</style>
