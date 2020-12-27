<script>
import markdown from './markdownRenderer'
export default {
  functional: true,
  props: {
    source: {
      required: true,
      type: String,
    },
  },
  render (h, { props, data }) {
    if (data.style || data.class || data.staticClass) {
      throw new Error('Markdown component does not support style or class attributes')
    }
    return h('div', {
      class: {
        markdown: true,
      },
      domProps: {
        innerHTML: markdown.render(props.source),
      },
    })
  },
}
</script>
