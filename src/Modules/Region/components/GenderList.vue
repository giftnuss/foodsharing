<template>
  <div class="container bootstrap">
    <div class="card mb-3 rounded">
      <div
        class="card-header text-white bg-primary"
      >
        {{ $i18n('genderlist.header_for_district', {bezirk: regionName}) }}
      </div>
    </div>
  </div>
</template>

<script>

  export default {
    props: {
      regionName: {
        type: String,
        default: ''
      },
      genderData: {
        type: Array,
        default: () => []
      },
      isWorkGroup: {
        type: Boolean,
        default: false
      }
    }
  }
</script>
