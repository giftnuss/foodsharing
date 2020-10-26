<template>
  <!-- eslint-disable-next-line vue/max-attributes-per-line -->
  <div class="infowrapper" :class="classes">
    <div v-if="member.joinDate">
      {{ $i18n('store.memberSince', { date: $dateFormat(member.joinDate, 'day') }) }}
    </div>
    <div v-if="member.fetchCount && member.lastPickup">
      {{ $i18n('store.lastPickup', { date: $dateFormat(member.lastPickup, 'day') }) }}
    </div>
    <div v-else-if="mayEditStore">
      {{ $i18n('store.noPickup') }}
    </div>
    <div v-if="member.isJumper">
      {{ $i18n('store.isJumper') }}
    </div>
    <div v-if="!member.isVerified">
      {{ $i18n('store.isNotVerified') }}
    </div>
    <div v-if="member.isManager">
      <b>{{ $i18n('store.isManager') }}</b>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    classes: { type: String, default: 'mh-50px' },
    mayEditStore: { type: Boolean, required: true },
    member: { type: Object, required: true },
  },
}
</script>

<style lang="scss" scoped>
.infowrapper {
  padding: 0;
  display: flex;
  flex-direction: column;
  justify-content: center;

  &.mh-50px {
    min-height: 50px;
  }

  div {
    font-size: smaller;
  }
}
</style>
