<template>
  <!-- eslint-disable vue/max-attributes-per-line -->
  <div class="blog-list-item d-flex align-items-center py-1">
    <div class="mx-1">
      <b-link
        :disabled="!mayPublish"
        @click="togglePublished"
      >
        <i
          v-b-tooltip.hover="$i18n(isPublished ? 'blog.status.1' : 'blog.status.0')"
          class="fas fa-fw"
          :class="[isPublished ? 'fa-check-square text-secondary' : 'fa-eye-slash text-primary']"
        />
      </b-link>
    </div>
    <div
      v-b-tooltip.hover
      class="text-muted mx-1"
      :title="$dateDistanceInWords(when)"
    >
      {{ $dateFormat(when, 'dd.MM.yyyy') }}
    </div>
    <div class="mx-2">
      {{ blogTitle }}
    </div>
    <b-link
      v-if="mayEdit"
      v-b-tooltip="$i18n('blog.edit')"
      class="mx-1"
      :href="$url('blogEdit', blogId)"
    >
      <i class="fas fa-fw fa-pencil-alt" />
    </b-link>
    <b-button
      v-if="mayDelete"
      v-b-tooltip="$i18n('blog.delete')"
      href="#"
      size="sm"
      class="ml-auto mr-1"
      variant="outline-danger"
      @click.prevent="removeBlogpost"
    >
      <i class="fas fa-fw fa-trash-alt" />
    </b-button>
  </div>
</template>

<script>
import dateFnsParseISO from 'date-fns/parseISO'
import $ from 'jquery'

import { deleteBlogpost } from '@/api/blog'
import i18n from '@/i18n'
import { hideLoader, showLoader, pulseSuccess } from '@/script'

export default {
  props: {
    blogId: { type: Number, required: true },
    blogTitle: { type: String, default: '' },
    published: { type: Boolean, required: true },
    regionId: { type: Number, required: true },
    createdAt: { type: String, required: true },
    mayPublish: { type: Boolean, default: true }, // this actually depends on the regionId...
    mayEdit: { type: Boolean, default: true },
    mayDelete: { type: Boolean, default: true },
  },
  data () {
    return {
      isPublished: this.published,
      when: dateFnsParseISO(this.createdAt.replace(' ', 'T')),
    }
  },
  methods: {
    togglePublished () {
      // legacy v_activeSwitcher callback waiting for REST reimplementation of xhr_activeSwitch
      showLoader()
      $.ajax({
        dataType: 'json',
        data: { t: 'blog_entry', id: this.blogId, value: parseInt(!this.isPublished) },
        url: '/xhr.php?f=activeSwitch',
        complete: () => {
          this.isPublished = !this.isPublished
          hideLoader()
        },
      })
    },
    async removeBlogpost () {
      if (!confirm(i18n('blog.confirmDelete', { name: this.blogTitle }))) {
        return
      }
      showLoader()
      await deleteBlogpost(this.blogId)
      hideLoader()
      pulseSuccess(i18n('success'))
      this.$emit('remove-blogpost-from-list', this.blogId)
    },
  },
}
</script>

<style lang="scss" scoped>
.blog-list-item {
  &, div {
    font-size: 0.875rem;
  }
}
</style>
