<script>
import markdown from './markdownRenderer'
export default {
  functional: true,
  props: {
    source: {
      required: true,
      type: String
    }
  },
  render (h, { props, data }) {
    if (data.style || data.class || data.staticClass) {
      throw new Error('Markdown component does not support style or class attributes')
    }
    return h('div', {
      class: {
        markdown: true
      },
      domProps: {
        innerHTML: markdown.render(props.source)
      }
    })
  }
}
</script>

<!-- UNSCOPED CSS! -->
<style lang="scss">
.markdown > {
  overflow-wrap: break-word;
}
.markdown > img.emoji {
  height: 1em;
  width: 1em;
  margin: 0 0.05em 0 0.1em;
  vertical-align: -0.1em;
}
.markdown > a {
  text-decoration: underline;
  opacity: 0.9;
}
.markdown > a:hover {
  opacity: 1;
}
.markdown > a:hover.fas-after::after {
  opacity: 1;
}
.markdown > a.fas-after::after {
  padding-left: 4px;
  font-family: 'Font Awesome 5 Free';
  font-weight: 900;
  font-size: 80%;
  display: inline-block;
  text-decoration: none;
  opacity: 0.7;
}
.markdown > a.fa-after-external-link::after {
  content: "\F35D";
}
.markdown > a.fa-after-envelope::after {
  content: "\F0E0";
}
.markdown > a.fa-after-phone::after {
  content: "\F095";
}
.markdown > p {
  margin-bottom: 0.5rem;
}
.markdown > h1 {
  font-size: 1.6em;
}
.markdown > h2 {
  font-size: 1.3em;
}
.markdown > h3 {
  font-size: 1.2em;
}
.markdown > h4 {
  font-size: 1.1em;
}
.markdown > h5,
.markdown > h6 {
  font-size: 1em;
}
.markdown > pre {
  font-size: 0.8em;
  padding: 3px;
  background-color: #ededed;
  margin-top: 0.5rem;
  margin-bottom: 0.5rem;
  overflow-x: auto;
  overflow-wrap: normal;
  display: block;
}
.markdown > p > code {
  font-size: 0.8em;
  padding-left: 3px;
  padding-right: 3px;
  background-color: #ededed;
  overflow-x: auto;
  overflow-wrap: normal;
  display: block;
}
.markdown > img:not(.emoji) {
  max-height: 150px;
  max-width: 300px;
}
</style>
