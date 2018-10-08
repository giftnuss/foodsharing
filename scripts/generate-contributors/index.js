/**
 * This script is based on https://github.com/kentcdodds/all-contributors
 * ... but the generated markup from that tool did not make me happy, so it's reimplemented here.
 *
 * It uses the same data format though, so you can still use the `all-contributors add <username> <type>`
 * command. You have to install the tool yourself (e.g. globally with `npm install -g all-contributors-cli`).
 * See their docs for more info.
 *
 * For the emojis we use twemoji to get <img src="path/to/emoji.svg"> elements generated, instead of relying on
 * the markdown rendering of GitLab (as it added title attributes and prevented our special title attributes from
 * being displayed).
 *
 * You can browse the emojis at https://twitter.github.io/twemoji/preview.html and add/change the list below!
 */

const twemoji = require('twemoji')

const { join } = require('path')
const { readFileSync, writeFileSync } = require('fs')
const basedir = join(__dirname, '../..')

const config = JSON.parse(readFileSync(join(basedir, '.all-contributorsrc'), 'utf8'))

const { files, contributors } = config

const peoplePerRow = 6

const emojiWidth = 14

const widthPercentage = Math.floor(100 / peoplePerRow)
const assumedPageWidth = 600
const imageSize = Math.floor(assumedPageWidth / peoplePerRow)

const htmlRows = chunk(contributors, peoplePerRow).map(contributorsRow => {
  const htmlGroup = contributorsRow.map(contributor => {
    const { name, avatar_url: avatar, profile, contributions } = contributor
    return `
      <td border="0" align="center" valign="top" width="${widthPercentage}%">
        <div style="height: ${imageSize}px; width: ${imageSize}px;">
          <a href="${profile}">
            <img src="${avatar}" width="${imageSize}px">
          </a><br>
        </div>
        ${contributions.sort().map(formatContribution).join('&nbsp;')}<br>
        <a href="${profile}">
          <sub>${mapName(name)}</sub>
        </a>
      </td>`
  }).join('\n')
  return `
    <tr border="0">
        ${htmlGroup}
    </tr>`
}).join('\n')

function mapName (name) {
  return name === 'OnceUponAFoodsharingTime' ? 'Once&#8203;Upon&#8203;A&#8203;Foodsharing&#8203;Time' : name
}

function formatContribution (type) {
  const contribution = contributionType(type)
  const img = twemoji.parse(contribution.symbol, {
    folder: 'svg',
    ext: '.svg'
  }).replace('<img', `<img width="${emojiWidth}px"`)
  return `<span title="${contribution.description}">${img}</span>`
}

const html = `
<table border="0">
  <tbody>
    ${htmlRows}
  </tbody>
</table>
`.replace(/^\s+$/gm, '').replace(/\n\n+/g, '\n')

for (const file of files) {
  const previousContent = readFileSync(join(basedir, file), 'utf8')
  const newContent = injectListBetweenTags(previousContent, html)
  writeFileSync(join(basedir, file), newContent)
  console.log('updated', file)
}

function chunk (array, n) {
  const result = []
  for (let i = 0, j = array.length; i < j; i += n) {
    result.push(array.slice(i, i + n))
  }
  return result
}

function injectListBetweenTags (previousContent, newContent) {
  const tagToLookFor = '<!-- FOODSHARING-CONTRIBUTORS-LIST:'
  let closingTag = '-->'
  const startOfOpeningTagIndex = previousContent.indexOf(`${tagToLookFor}START`)
  const endOfOpeningTagIndex = previousContent.indexOf(closingTag, startOfOpeningTagIndex)
  const startOfClosingTagIndex = previousContent.indexOf(`${tagToLookFor}END`, endOfOpeningTagIndex)
  if (startOfOpeningTagIndex === -1 || endOfOpeningTagIndex === -1 || startOfClosingTagIndex === -1) {
    return previousContent
  }
  return [previousContent.slice(0, endOfOpeningTagIndex + closingTag.length), newContent, previousContent.slice(startOfClosingTagIndex)].join('')
}

// These are not official keys, but ones we added
function getExtraTypes () {
  return {
    security: {
      symbol: '🔐',
      description: 'Security'
    },
    board: {
      symbol: '🏢',
      description: 'Board member'
    }
  }
}

// These keys should match the official all-contributers ones (but can change the icons/descriptions)
function contributionType (type) {
  return {
    ...getExtraTypes(),
    blog: {
      symbol: '📝',
      description: 'Blogposts'
    },
    bug: {
      symbol: '🐜',
      description: 'Bug reports'
    },
    code: {
      symbol: '💻',
      description: 'Code'
    },
    design: {
      symbol: '🎨',
      description: 'Design'
    },
    doc: {
      symbol: '📝',
      description: 'Documentation'
    },
    eventOrganizing: {
      symbol: '📋',
      description: 'Event Organizing'
    },
    example: {
      symbol: '💡',
      description: 'Examples'
    },
    financial: {
      symbol: '💵',
      description: 'Financial'
    },
    fundingFinding: {
      symbol: '🔍',
      description: 'Funding Finding'
    },
    ideas: {
      symbol: '💡',
      description: 'Ideas, Planning, & Feedback'
    },
    infra: {
      symbol: '🔩',
      description: 'Infrastructure (Hosting, Build-Tools, etc)'
    },
    platform: {
      symbol: '📦',
      description: 'Packaging/porting to new platform'
    },
    plugin: {
      symbol: '🔌',
      description: 'Plugin/utility libraries'
    },
    question: {
      symbol: '💬',
      description: 'Answering Questions'
    },
    review: {
      symbol: '👀',
      description: 'Reviewed Pull Requests'
    },
    talk: {
      symbol: '📢',
      description: 'Talks'
    },
    test: {
      symbol: '⚠️',
      description: 'Tests'
    },
    tool: {
      symbol: '🔧',
      description: 'Tools'
    },
    translation: {
      symbol: '🌍',
      description: 'Translation'
    },
    tutorial: {
      symbol: '✅',
      description: 'Tutorials'
    },
    video: {
      symbol: '📹',
      description: 'Videos'
    }
  }[type] || typeMissing(type)
}

function typeMissing (type) {
  throw new Error(`no contribution type [${type}]`)
}
