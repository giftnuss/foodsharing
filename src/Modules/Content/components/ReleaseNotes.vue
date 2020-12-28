<template>
  <!-- eslint-disable vue/max-attributes-per-line -->
  <div class="bootstrap">
    <div class="container">
      <div class="d-flex justify-content-between align-items-center py-2">
        <h3>
          <i class="fas fa-fw fa-magic" />
          {{ $i18n('menu.entry.release-notes') }}
        </h3>
        <b-button
          size="sm"
          variant="secondary"
          :href="$url('changelog')"
        >
          <i class="fas fa-fw fa-file-alt" />
          {{ $i18n('content.changelog') }}
        </b-button>
      </div>

      <div id="releases" class="clear" role="tablist">
        <b-card no-body class="p-3">
          <ReleaseNotesEntry
            v-for="r in releaseList"
            :key="r.id"
            :release-id="r.id"
            :release-title="r.title"
            :markdown-content="r.markdown"
            :is-visible="r.visible"
          />
        </b-card>
      </div>
    </div>
  </div>
</template>

<script>
import ReleaseNotesEntry from './ReleaseNotesEntry.vue'

export default {
  components: { ReleaseNotesEntry },
  props: {
    releaseList: { type: Array, default: () => { return [] } },
  },
}
</script>

<style lang="scss" scoped>
.container #releases {
  ::v-deep .markdown {
    font-size: 0.875rem;

    p, div, ul, ol, li, strong, em {
      font-size: inherit;
    }
  }
}
</style>
