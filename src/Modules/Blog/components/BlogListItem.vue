<template>
  <!-- eslint-disable vue/max-attributes-per-line -->
  <div class="blog-list-item d-flex align-items-center py-2">
    <div class="mx-1">
      <b-link
        :disabled="!mayPublish"
        @click="togglePublished"
      >
        <i
          v-b-tooltip.hover="$i18n(isPublished ? 'blog.status.1' : 'blog.status.0')"
          class="fas fa-fw"
          :class="[isPublished ? 'fa-check-square text-secondary' : 'fa-clock text-primary']"
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
    <div class="mx-1">
      {{ blogTitle }}
    </div>
    <b-link
      v-if="mayEdit"
      v-b-tooltip="$i18n('blog.edit')"
      :href="$url('blogEdit', blogId)"
    >
      <i class="fas fa-fw fa-pencil-alt" />
    </b-link>
  </div>
</template>

<script>
import dateFnsParseISO from 'date-fns/parseISO'
import $ from 'jquery'

import { hideLoader, showLoader } from '@/script'

export default {
  props: {
    blogId: { type: Number, required: true },
    blogTitle: { type: String, default: '' },
    published: { type: Boolean, required: true },
    regionId: { type: Number, required: true },
    createdAt: { type: String, required: true },
    mayPublish: { type: Boolean, default: true }, // this actually depends on the regionId...
    mayEdit: { type: Boolean, default: true },
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
