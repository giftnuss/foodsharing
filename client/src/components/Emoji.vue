<template>
  <span class="emoji">
     {{ emojis[name] }}
  </span>
</template>

<script>
import emojiList from '@/emojiList.json'

// TODO: 
export default {
  props: ['name'],
  data() {
      return {
          emojis: emojiList
      }
  }
}
</script>
