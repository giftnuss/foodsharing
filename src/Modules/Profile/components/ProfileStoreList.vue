<template>
  <div class="infos store-list bootstrap">
    <b-card
      :header="$i18n('profile.nav.storelist', { count: stores.length })"
      header-class="font-weight-bold"
      no-body
    >
      <b-list-group flush>
        <b-list-group-item
          v-for="store in stores"
          :key="store.id"
          class="d-flex justify-content-between align-items-center"
          :href="$url('store', store.id)"
          :variant="store.active === 2 ? 'light' : 'default'"
        >
          <i
            v-if="store.isManager"
            v-b-tooltip.hover="$i18n('store.isManager')"
            class="fas fa-fw fa-cog"
          />
          <i
            v-else-if="store.active === 2"
            v-b-tooltip.hover="$i18n('store.isJumper')"
            class="fas fa-fw fa-star"
          />
          <i
            v-else-if="store.active === 1"
            class="fas fa-fw fa-shopping-cart invisible"
          />
          <i
            v-else
            v-b-tooltip.hover
            title="appliedFor"
            class="fas fa-fw fa-question-circle"
          />

          <span class="flex-grow-1 p-1 pl-2">
            {{ store.name }}
          </span>
        </b-list-group-item>
      </b-list-group>
    </b-card>
  </div>
</template>

<script>
export default {
  props: {
    stores: { type: Array, default: () => { return [] } },
  },
}
</script>

<style lang="scss" scoped>
.store-list {
  margin: -10px; // offset ui-padding for now (until profile is built with PageHelper)

  ::v-deep .list-group-item {
    padding: 0 0.5rem;
  }
}
</style>
