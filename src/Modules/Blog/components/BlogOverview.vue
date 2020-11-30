<template>
  <div class="bootstrap">
    <div class="card rounded">
      <div
        class="card-header bg-primary text-white d-flex justify-content-between align-items-center mb-2"
        :class="{'can-write': canWriteNewBlog}"
      >
        <div class="font-weight-bolder">
          {{ $i18n('blog.title') }}
        </div>
        <b-button
          v-if="canWriteNewBlog"
          v-b-tooltip
          :title="$i18n('blog.new')"
          class="btn btn-secondary btn-sm write-new ml-1"
        >
          <i class="fas faw fa-plus" />
        </b-button>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    canWriteNewBlog: { type: Boolean, default: false },
  },
}
</script>

<style lang="scss" scoped>
.card-header.can-write {
  padding-top: 0;
  padding-bottom: 0;
}

::v-deep .write-new {
  margin-right: -8px;
}
</style>
