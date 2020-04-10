<template>
  <div id="footer">
    <div class="linkwrapper">
      <div class="footerbox">
        <ul>
          <li>
            <a
              :href="$url('team')"
              :aria-label="$i18n('footer.team')"
            >
              {{ $i18n('footer.team') }}
            </a>
          </li>
          <li>
            <a
              :href="$url('imprint')"
              :aria-label="$i18n('footer.imprint')"
            >
              {{ $i18n('footer.imprint') }}
            </a>
          </li>
          <li>
            <a
              :href="$url('dataprivacy')"
              :aria-label="$i18n('footer.dataprivacy')"
            >
              {{ $i18n('footer.dataprivacy') }}
            </a>
          </li>
          <li>
            <a
              :href="$url('faq')"
              :aria-label="$i18n('footer.faq')"
            >
              {{ $i18n('footer.faq') }}
            </a>
          </li>
        </ul>
      </div>
      <div class="footerbox">
        <ul>
          <li>
            <a
              :href="$url('guide')"
              :aria-label="$i18n('footer.guide')"
            >
              {{ $i18n('footer.guide') }}
            </a>
          </li>
          <li>
            <a
              :href="$url('infosCompany')"
              :aria-label="$i18n('footer.infosCompany')"
            >
              {{ $i18n('footer.infosCompany') }}
            </a>
          </li>
          <li>
            <a
              :href="$url('partner')"
              :aria-label="$i18n('footer.partner')"
            >
              {{ $i18n('footer.partner') }}
            </a>
          </li>
          <li>
            <a
              :href="$url('statistics')"
              :aria-label="$i18n('footer.statistics')"
            >
              {{ $i18n('footer.statistics') }}
            </a>
          </li>
        </ul>
      </div>
      <div class="footerbox donate">
        <ul>
          <li>
            <a
              :href="$url('donate')"
              :aria-label="$i18n('footer.donate')"
            >
              {{ $i18n('footer.donate') }}
            </a>
          </li>
          <li>
            <form
              action="https://www.paypal.com/cgi-bin/webscr"
              method="post"
              target="_top"
            >
              <input
                type="hidden"
                name="cmd"
                value="_s-xclick"
              >

              <input
                type="hidden"
                name="hosted_button_id"
                value="CLPZCSCKGNXE4"
              >

              <input
                type="image"
                src="/img/btn_donateCC_LG.gif"
                border="0"
                name="submit"
                alt="Jetzt einfach, schnell und sicher online spenden – mit PayPal."
              >
            </form>
          </li>
        </ul>
      </div>

      <div
        v-if="isFsDotAt"
        class="footerbox thanks"
      >
        <span class="thanks-at">
          <a
            href="http://www.bmlfuw.gv.at/"
            target="_blank"
            rel="nofollow noopener"
          ><img
            src="/img/mfelo.png"
            alt="Lebensministerium"
          ></a>
          <a
            href="http://www.wienertafel.at/"
            target="_blank"
            rel="nofollow noopener"
          ><img
            src="/img/wien_tafel.png"
            alt="Wiener Tafel"
          ></a>
          <a
            href="http://www.denns-biomarkt.at/"
            target="_blank"
            rel="nofollow noopener"
          ><img
            src="/img/denns_logo_100.png"
            alt="Denn's Biomarkt"
          ></a>
        </span>
      </div>

      <div class="footerbox thanks">
        <h6 class="heading">
          {{ $i18n('footer.thanksHoster') }}
        </h6>
        <a
          class="imglink"
          href="https://www.manitu.de/"
          target="_blank"
          rel="nofollow noopener"
        ><img
          src="/img/manitu_logo.png"
          alt="Manitu"
        ></a>
      </div>
    </div>

    <div class="below">
      <span
        v-if="srcRevision"
        class="rev-link"
      >
        <a :href="revisionLink">
          {{ $i18n('footer.version', {version: srcRevision}) }}
        </a>
      </span>
      <span
        v-else
        class="rev-link"
      >
        <a href="https://gitlab.com/foodsharing-dev/">
          Hello World! This website is under active development!
        </a>
      </span>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    isFsDotAt: {
      type: Boolean,
      default: false
    },
    srcRevision: {
      type: String,
      default: null
    }
  },
  computed: {
    revisionLink () {
      return 'https://gitlab.com/foodsharing-dev/foodsharing/tree/' + this.srcRevision
    }
  }
}
</script>

<style lang="scss" scoped>
#footer {
  padding-top: 20px;
  padding-bottom: 20px;
  background-color: var(--fs-brown);
  box-shadow: inset 0 20px 20px -20px rgba(0, 0, 0, 0.75);
}

.linkwrapper {
  display: flex;
  flex-flow: row wrap;
  margin: 0 auto;
  max-width: 1200px;
  justify-content: space-around;
  align-items: center;

  .footerbox {
    display: flex;
    flex-flow: column wrap;

    ul {
      list-style-type: none;

      li {
        flex: 1 1 auto;

        a {
          display: inline-block;
          width: 100%;
          padding: 6px 9px;
          border-radius: 6px;
          background-color: var(--fs-brown);
          color: var(--fs-beige);
          text-decoration: none;

          &:hover {
            background-color: var(--fs-white);
            color: var(--fs-brown);
          }
        }
      }
    }

    &.thanks {
      color: var(--fs-white);
    }

    .imglink {
      background-color: var(--fs-white);
      padding: 6px;
      display: inline-block;
    }
  }
}

.below {
  color: var(--fs-beige);
  margin-right: 30px;
  text-align: right;
}
</style>
