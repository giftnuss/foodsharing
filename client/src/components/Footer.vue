<template>
  <!-- eslint-disable-next-line vue/max-attributes-per-line -->
  <b-container
    fluid
    class="bootstrap bg-white pt-4 pb-5"
  >
    <b-container>
      <b-row>
        <b-col
          xl="3"
          sm="3"
          cols="12"
        >
          <h2>{{ $i18n('footer.call_to_action') }}</h2>
          <ul class="nav flex-column">
            <li class="nav-item">
              <a
                :href="$url('infosCompany')"
                :aria-label="$i18n('footer.infosCompany')"
                class="nav-link"
              >
                {{ $i18n('footer.infosCompany') }}
              </a>
            </li>
            <li class="nav-item">
              <a
                :href="$url('joininfo')"
                :aria-label="$i18n('footer.infosPrivatePersons')"
                class="nav-link"
              >
                {{ $i18n('footer.infosPrivatePersons') }}
              </a>
            </li>
          </ul>
        </b-col>
        <b-col
          xl="3"
          sm="4"
          cols="12"
          class="pt-5 pt-sm-0"
        >
          <h2>{{ $i18n('footer.about_us') }}</h2>
          <b-row>
            <b-col
              sm="4"
              cols="3"
            >
              <ul class="nav flex-column">
                <li class="nav-item">
                  <a
                    :href="$url('team')"
                    :aria-label="$i18n('footer.team')"
                    class="nav-link"
                  >
                    {{ $i18n('footer.team') }}
                  </a>
                </li>
                <li class="nav-item">
                  <a
                    :href="$url('partner')"
                    :aria-label="$i18n('footer.partner')"
                    class="nav-link"
                  >
                    {{ $i18n('footer.partner') }}
                  </a>
                </li>
                <li class="nav-item">
                  <a
                    :href="$url('contact')"
                    :aria-label="$i18n('footer.contact')"
                    class="nav-link"
                  >
                    {{ $i18n('footer.contact') }}
                  </a>
                </li>
                <li class="nav-item">
                  <a
                    :href="$url('press')"
                    :aria-label="$i18n('footer.press')"
                    class="nav-link"
                  >
                    {{ $i18n('footer.press') }}
                  </a>
                </li>
              </ul>
            </b-col>
            <b-col
              xl="2"
              sm="3"
              cols="3"
            >
              <ul class="nav flex-column">
                <li class="nav-item">
                  <a
                    :href="$url('statistics')"
                    :aria-label="$i18n('footer.statistics')"
                    class="nav-link"
                  >
                    {{ $i18n('footer.statistics') }}
                  </a>
                </li>
                <li class="nav-item">
                  <a
                    :href="$url('wiki')"
                    :aria-label="$i18n('footer.wiki')"
                    class="nav-link"
                  >
                    {{ $i18n('footer.wiki') }}
                  </a>
                </li>
                <li class="nav-item">
                  <a
                    :href="$url('blog')"
                    :aria-label="$i18n('footer.blog')"
                    class="nav-link"
                  >
                    {{ $i18n('footer.blog') }}
                  </a>
                </li>
              </ul>
            </b-col>
          </b-row>
        </b-col>
        <b-col
          xl="6"
          sm="5"
          cols="12"
          class="pt-4 pt-xl-5 pt-sm-0"
        >
          <b-row>
            <b-col
              v-if="isFsDotAt"
              xl="6"
              cols="12"
            >
              <p>{{ $i18n('footer.our_partner') }}</p>
              <a
                href="$url('hoster')"
                target="_blank"
                :rel="externalLink"
                data-toggle="tooltip"
                data-placement="top"
                :title="$i18n('footer.hosting_tooltip')"
              >
                <img src="/img/manitu_logo_at.png"></a>
              <a
                href="$url('denns')"
                target="_blank"
                :rel="externalLink"
              >
                <img
                  src="/img/denns_logo_at.png"
                  class="pl-4"
                  :alt="$i18n('footer.denns_at_alt_text')"
                >
              </a>
              <br>
              <a
                href="$url('bmlfuw')"
                target="_blank"
                :rel="externalLink"
              >
                <img
                  src="/img/lebensministerium_Logo_2014_at.png"
                  :alt="$i18n('footer.lebensministerium_alt_text')"
                  class="pt-4"
                >
              </a>
              <a
                href="$url('wiener_tafel')"
                target="_blank"
                :rel="externalLink"
              >
                <img
                  src="/img/wiener_tafel_logo_at.png"
                  class="pl-4 pt-4"
                  :alt="$i18n('footer.wiener_tafel_alt_text')"
                >
              </a>
            </b-col>
            <b-col
              v-else
              xl="6"
              cols="12"
            >
              <p>{{ $i18n('footer.hosting_info') }}</p>
              <a
                :href="$url('hoster')"
                target="_blank"
                :rel="externalLink"
              >
                <img src="/img/manitu_logo.png">
              </a>
            </b-col>
            <b-col
              xl="6"
              cols="12"
              class="pt-xl-0 pt-5"
            >
              <ul>
                <li>
                  <p>{{ $i18n('footer.call_to_donate') }}</p>
                </li>
                <li>
                  <b-button
                    block
                    variant="secondary"
                    :href="$url('donations')"
                  >
                    {{ $i18n('footer.donate_now') }}
                  </b-button>
                </li>
              </ul>
            </b-col>
          </b-row>
        </b-col>
      </b-row>
      <div class="line pt-3 pt-xl-5" />
      <b-row class="pt-3">
        <b-col
          xl="8"
          cols="6"
        >
          <b-row>
            <b-col
              xl="4"
              sm="8"
              cols="3"
            >
              <a
                :href="$url('imprint')"
                :aria-label="$i18n('footer.imprint')"
                class="pr-sm-4"
              >
                {{ $i18n('footer.imprint') }}
              </a>
              <a
                :href="$url('dataprivacy')"
                :aria-label="$i18n('footer.dataprivacy')"
              >
                {{ $i18n('footer.dataprivacy') }}
              </a>
            </b-col>
            <b-col
              xl="2"
              sm="4"
            >
              <a
                v-if="!isFsDotAt"
                class="social_icons"
                :href="twitterUrl"
                target="_blank"
                :rel="externalLink"
              >
                <i class="fab fa-twitter-square" />
              </a>
              <a
                class="social_icons"
                :href="facebookUrl"
                target="_blank"
                :rel="externalLink"
              >
                <i class="fab fa-facebook-square pl-2" />
              </a>
              <a
                class="social_icons"
                :href="instagramUrl"
                target="_blank"
                :rel="externalLink"
              >
                <i class="fab fa-instagram-square pl-2" />
              </a>
            </b-col>
          </b-row>
        </b-col>
        <b-col
          xl="4"
          cols="6"
          class="d-flex justify-content-end"
        >
          <p>
            {{ $i18n('footer.it_slogan') }}
            <i class="fas fa-heart social_icons" />
            <a :href="$url('devdocs')">
              {{ $i18n('footer.it_slogan_from') }}
            </a>
          </p>
        </b-col>
      </b-row>
      <b-row>
        <b-col>
          <div class="text-right">
            <span
              v-if="srcRevision && isBeta"
              class="rev-link"
            >
              <a :href="revisionLink">
                {{ $i18n('footer.version', {version: srcRevision}) }}
              </a>
            </span>
          </div>
        </b-col>
      </b-row>
    </b-container>
  </b-container>
</template>

<script>
export default {
  props: {
    isFsDotAt: {
      type: Boolean,
      default: false,
    },
    srcRevision: {
      type: String,
      default: null,
    },
    isBeta: {
      type: Boolean,
      default: false,
    },
  },
  computed: {
    revisionLink () {
      return this.isBeta ? 'https://gitlab.com/foodsharing-dev/foodsharing/tree/' + this.srcRevision : ''
    },
    facebookUrl () {
      return this.isFsDotAt ? 'https://www.facebook.com/oesterreichfoodsharing' : 'https://www.facebook.com/foodsharing.de'
    },
    instagramUrl () {
      return this.isFsDotAt ? 'https://instagram.com/foodsharing.at' : 'https://www.instagram.com/foodsharing.de_offiziell'
    },
    twitterUrl () {
      return 'https://twitter.com/FoodsharingDE'
    },
    externalLink () {
      return 'nofollow noreferrer noopener'
    },
  },
}
</script>

<style lang="scss" scoped>
.bootstrap .nav-link {
  padding: unset;
}

.social_icons {
  color: var(--fs-light-grey);
  font-size: 1.2rem;
}

ul {
  list-style-type: none;
  margin-left: 0;
}

h2 {
  font-family: Alfa Slab One,serif;
  font-style: normal;
  font-weight: normal;
  font-size: 1.1rem;
  color: var(--fs-dark-grey);
}

.line {
  border-bottom: 1px solid var(--fs-dark-grey);
}

a, p, li {
  font-family: Noto Sans;
  font-size: 1rem;
  line-height: 2rem;
  color: var(--fs-black);
}

p {
  line-height: 1rem;
}
</style>
