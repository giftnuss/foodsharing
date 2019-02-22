import markdownIt from 'markdown-it'

const md = markdownIt('zero', {
  html: true,
  breaks: true,
  linkify: true,
  typopgrapher: true,
  quotes: '“”‘’'
})
  .enable([
    'heading',
    'emphasis',
    'strikethrough',
    'blockquote',
    'newline',
    'image',
    'link',
    'backticks',
    'linkify',
    'hr',
    'list',
    'fence',
    'code'
  ])

export default md
