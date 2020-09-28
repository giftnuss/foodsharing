<template>
  <a
    :class="bellClasses"
    :href="bell.href"
    @click="$emit('bellRead', bell)"
    @click.middle="$emit('bellRead', bell)"
  >
    <div class="bell-grid">
      <div class="bell-icon">
        <i
          v-if="bell.icon"
          :class="['icon', `${bell.icon}`, {'hideonhover': bell.isCloseable}]"
        />
        <div
          v-if="bell.image"
          :class="['icon', {'hideonhover': bell.isCloseable}]"
        >
          <img :src="bell.image">
        </div>
        <a
          v-if="bell.isCloseable"
          class="icon showonhover"
          href="#"
          @click.stop.prevent="$emit('remove', bell.id)"
        >
          <i
            class="icon fas fa-times"
          />
        </a>
        <!-- <div :class="['avatar', 'avatar_'+avatars.length]">
                          <div v-for="avatar in avatars" :key="avatar" :style="{backgroundImage: `url('${avatar}')`}" />
                      </div> -->
      </div>
      <small class="bell-date text-muted">
        {{ $dateDistanceInWords(bell.createdAt) }}
      </small>
      <div class="bell-body mt-1">
        <h5 class="bell-title mb-1">
          {{ $i18n(`bell.${bell.title}`, bell.payload) }}
        </h5>
        <p class="bell-text mb-1">
          {{ $i18n(`bell.${bell.key}`, bell.payload) }}
        </p>
      </div>
    </div>
  </a>
</template>

<script>
export default {
  props: {
    bell: {
      type: Object,
      default: () => ({})
    }
  },
  computed: {
    bellClasses () {
      return [
        'list-group-item',
        'list-group-item-action',
        'flex-row',
        !this.bell.isRead ? 'list-group-item-warning' : null,
        this.bell.isDeleting ? 'disabledLoading' : null
      ]
    }
  }
}
</script>

<style lang="scss" scoped>
.list-group-item {
  padding: 0.4em 0.6em;
}

.icon {
  height: 100%;
  min-height: 1.5em;
  min-width: 1.5em;
  text-align: center;

  @supports (display: grid) {
    display: grid;

    &::before,
    i.fas::before {
      grid-row-start: none;
    }
  }

  img {
    width: 100%;
  }
}

.showonhover { display: none; }

.list-group-item:hover {
  .icon { text-decoration: none; }
  .hideonhover { display: none; }
  .showonhover { display: inline-block; }
  @supports (display: grid) {
    .showonhover { display: grid; }
  }
}

.bell-grid {
  @supports (display: grid) {
    display: grid;
    grid-template-columns: 45px 1fr 80px;
  }

  .bell-icon {
    height: 100%;
    grid-row-start: 1;
    grid-row-end: 3;
    align-self: center;
    justify-self: center;
    font-size: 2em;
  }

  .bell-title {
    margin-right: 60px;
    font-weight: bold;
    font-size: 0.9em;
  }

  .bell-text {
    font-size: 0.8em;
  }

  .bell-body {
    grid-column-start: 2;
    grid-column-end: 4;
    grid-row-start: 1;
    grid-row-end: 3;
  }

  .bell-date {
    margin-right: -5px;
    text-align: right;
    grid-column-start: 3;
    grid-column-end: 4;
    grid-row-start: 1;
    grid-row-end: 2;
    z-index: 2;
  }
}
</style>
