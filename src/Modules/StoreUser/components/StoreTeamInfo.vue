<template>
  <div class="team-memberinfo">
    <!-- eslint-disable-next-line vue/max-attributes-per-line -->
    <b-tooltip :target="`member-${user.id}`" triggers="hover blur">
      <div v-if="user.isManager">
        {{ $i18n('store.isManager') }}
      </div>
      <div v-if="user.joinDate">
        {{ $i18n('store.memberSince', { date: $dateFormat(user.joinDate, 'day') }) }}
      </div>
      <div v-if="user.fetchCount && user.lastPickup">
        {{ $i18n('store.lastPickup', { date: $dateFormat(user.lastPickup, 'day') }) }}
      </div>
      <div v-else-if="!user.fetchCount">
        {{ $i18n('store.noPickup') }}
      </div>
      <div v-else-if="user.isJumper">
        {{ $i18n('store.isJumper') }}
      </div>
      <div v-else-if="!user.isVerified">
        {{ $i18n('store.isNotVerified') }}
      </div>
    </b-tooltip>
    <a
      :id="`member-${user.id}`"
      href="#memberdetails"
      class="member-info"
      :class="{'jumper': user.isJumper}"
      @click.prevent="$emit('toggle-details')"
    >
      <span class="member-name">
        {{ user.name }}
      </span>
      <span class="member-phone">
        {{ user.number }}
      </span>
      <span
        v-if="user.phone && (user.phone !== user.number)"
        class="member-phone"
      >
        {{ user.phone }}
      </span>
      <span
        v-if="user.fetchCount && user.lastPickup"
        :class="[storeManagerView ? 'font-weight-bolder' : 'text-muted']"
      >
        {{ storeManagerView ?
          $i18n('store.lastPickup', { date: $dateFormat(user.lastPickup, 'day') }) :
          $i18n('store.lastPickupShort', { date: $dateDistanceInWords(user.lastPickup) }) }}
      </span>
      <span
        v-else-if="user.joinDate && storeManagerView"
        class="text-muted"
      >
        {{ $i18n('store.memberSince', { date: $dateFormat(user.joinDate, 'day') }) }}
      </span>
    </a>
  </div>
</template>

<script>
export default {
  props: {
    user: { type: Object, required: true },
    storeManagerView: { type: Boolean, default: false }
  }
}
</script>

<style lang="scss" scoped>
.member-info {
  display: flex;
  min-height: 50px;
  padding-left: 10px;
  flex-direction: column;
  justify-content: center;
  font-size: smaller;
  color: var(--dark);

  &:hover, &:focus {
    text-decoration: none;
    outline-color: var(--fs-brown);
  }

  .member-name {
    padding-left: 1px;
    min-width: 0;
    word-break: break-word;
    font-weight: bolder;
  }
}
</style>
