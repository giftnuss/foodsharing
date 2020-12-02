<template>
  <!-- eslint-disable vue/max-attributes-per-line -->
  <div class="blog-list-item d-flex align-items-center py-1 flex-wrap flex-sm-nowrap">
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
    <div class="mx-1 flex-shrink-0">
      <b-link
        v-b-tooltip.hover="$i18n('blog.author')"
        class="blog-editor text-primary"
        :href="$url('profile', authorId)"
      >
        <i class="fas fa-fw fa-user-edit" />
      </b-link>
      <b-link
        v-if="lastEditorId"
        v-b-tooltip.hover="$i18n('blog.last-editor')"
        class="blog-editor text-muted"
        :href="$url('profile', lastEditorId)"
      >
        <i class="fas fa-fw fa-pen-square" />
      </b-link>
    </div>
    <div class="mx-1 blog-text">
      <span class="blog-title ml-1">
        {{ blogTitle }}
      </span>
      <span class="blog-teaser d-inline-block mx-1 text-muted">
        {{ blogTeaser }}
      </span>
    </div>
    <b-link
      v-if="mayEdit"
      v-b-tooltip="$i18n('blog.edit')"
      class="ml-auto mx-1"
      :href="$url('blogEdit', blogId)"
    >
      <i class="fas fa-fw fa-pencil-alt" />
    </b-link>
    <b-button
      v-if="mayDelete"
      v-b-tooltip="$i18n('blog.delete')"
      href="#"
      size="sm"
      class="mx-1"
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
    blogTeaser: { type: String, default: '' },
    published: { type: Boolean, required: true },
    regionId: { type: Number, required: true },
    createdAt: { type: String, required: true },
    authorId: { type: Number, required: true },
    lastEditorId: { type: Number, default: null },
    mayEdit: { type: Boolean, default: false },
  },
  data () {
    return {
      isPublished: this.published,
      when: dateFnsParseISO(this.createdAt.replace(' ', 'T')),
      mayPublish: this.mayEdit,
      mayDelete: this.mayEdit,
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
  .blog-teaser {
    font-size: 0.75rem;
  }
  @media only screen and (max-width: 30rem) {
    .blog-text {
      flex-basis: 100%;
      order: 1;
    }
  }
}
</style>
