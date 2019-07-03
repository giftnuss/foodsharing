<template>
  <span class="emoji">
    {{ emojis[name] }}
  </span>
</template>

<script>
import emojiList from '@/emojiList.json'

// TODO:
export default {
  props: {
    'name': {
      type: String,
      default: null
    }
  },
  data () {
    return {
      emojis: emojiList
    }
  }
}
</script>
