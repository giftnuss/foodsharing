<!-- eslint-disable vue/max-attributes-per-line -->
<template>
  <div class="store-view bootstrap">
    <div class="card rounded mb-2">
      <div class="card-header text-white bg-primary">
        {{ $i18n('wall.name') }}
      </div>

      <div class="card-body p-0">
        <StoreWall
          :store-id="storeId"
          :managers="storeManagers"
          :may-write-post="mayWritePost"
          :may-delete-everything="mayDeleteEverything"
        />
      </div>
    </div>
  </div>
</template>

<script>
import StoreWall from './StoreWall'

export default {
  components: { StoreWall },
  props: {
    storeId: { type: Number, required: true },
    storeManagers: { type: Array, default: () => [] },
    mayWritePost: { type: Boolean, required: true },
    mayDeleteEverything: { type: Boolean, required: true }
  }
}
</script>

<style lang="scss" scoped>
</style>
