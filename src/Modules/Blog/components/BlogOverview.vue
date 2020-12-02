<template>
  <div class="bootstrap">
    <div class="card rounded">
      <div
        class="card-header bg-primary text-white d-flex justify-content-between align-items-center mb-1"
      >
        <div class="font-weight-bolder">
          {{ $i18n('blog.title') }}
        </div>

        <b-button-group class="header-buttons ml-1">
          <b-button
            v-b-tooltip.hover="$i18n('blog.go')"
            variant="secondary"
            size="sm"
            :href="$url('blog')"
            target="_blank"
          >
            <i class="fas faw fa-external-link-alt" />
          </b-button>
          <b-button
            v-if="mayAdministrateBlog"
            v-b-tooltip.hover="$i18n('blog.new')"
            class="write-new"
            variant="secondary"
            size="sm"
            :href="$url('blogAdd')"
          >
            <i class="fas faw fa-plus" />
          </b-button>
        </b-button-group>
      </div>
      <div class="card-body bg-white mb-2">
        <BlogListItem
          v-for="blog in blogposts"
          :key="blog.id"
          :blog-id="blog.id"
          :blog-title="blog.name"
          :blog-teaser="blog.teaser"
          :published="!!blog.active"
          :region-id="blog.bezirk_id"
          :created-at="blog.time"
          :author-id="blog.foodsaver_id"
          :may-edit="mayAdministrateBlog"
          @remove-blogpost-from-list="removeListItem"
        />
      </div>
    </div>
  </div>
</template>

<script>
import BlogListItem from './BlogListItem.vue'

export default {
  components: { BlogListItem },
  props: {
    mayAdministrateBlog: { type: Boolean, required: true },
    blogList: { type: Array, default: () => { return [] } },
  },
  data () {
    return {
      blogposts: this.blogList,
    }
  },
  methods: {
    removeListItem (blogId) {
      const index = this.blogposts.findIndex(b => b.id === blogId)
      if (index >= 0) {
        this.blogposts.splice(index, 1)
      }
    },
  },
}
</script>

<style lang="scss" scoped>
.card-header {
  padding-top: 0;
  padding-bottom: 0;
}

::v-deep .header-buttons {
  margin-right: -8px;
}
</style>
