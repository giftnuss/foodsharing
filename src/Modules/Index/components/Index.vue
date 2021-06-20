<template>
  <b-container
    fluid
    class="bootstrap bg-white"
  >
    <div class="card text-white pb-5">
      <div class="header-desktop" />
      <div class="card-img-overlay">
        <b-container>
          <b-row>
            <b-col
              lg="2"
              class="d-none d-lg-block"
            >
              <Fork />
            </b-col>
            <b-col
              xl="8"
              lg="10"
              cols="12"
              class="text-center mt-md-5"
            >
              <b-card-title class="mt-4 mt-md-0">
                <h1>{{ $i18n('startpage.welcome_text') }}</h1>
              </b-card-title>
              <b-card-text class="mt-4">
                <h3>{{ $i18n('startpage.slogan_1') }}</h3>
              </b-card-text>
              <b-card-text class="mt-5 text-center">
                <b-button
                  variant="secondary"
                  class="pl-5 pr-5"
                  :href="$url('joininfo')"
                >
                  {{ $i18n('startpage.call_to_action_button') }}
                </b-button>
              </b-card-text>
              <h4
                class="d-none d-lg-block"
              >
                <b>{{ $i18n('startpage.slogan_2') }}</b><br>
                {{ $i18n('startpage.slogan_3') }}
              </h4>
            </b-col>
          </b-row>
        </b-container>
      </div>
    </div>
    <b-row
      v-if="contentBlockVisible"
      class="ml-3 ml-md-5"
    >
      <b-col
        cols="12"
        md="12"
        xl="3"
        class="text-xl-right"
      >
        <span v-html="contentBlock1" />
      </b-col>
      <b-col
        cols="12"
        xl="5"
        md="6"
      >
        <span v-html="contentBlock2" />
      </b-col>
      <b-col
        cols="12"
        md="6"
        xl="4"
      >
        <span v-html="contentBlock3" />
      </b-col>
    </b-row>
    <div
      v-if="contentBlockVisible"
      class="line pt-3 pt-xl-5"
    />
    <b-row class="mt-3 ml-3 ml-md-5">
      <b-col
        cols="12"
        xl="3"
        class="text-xl-right"
      >
        <h2>{{ $i18n('startpage.mission.title') }}</h2>
      </b-col>
      <b-col
        cols="12"
        xl="6"
      >
        <p>
          {{ $i18n('startpage.mission.text') }}
        </p>
        <p>
          <b-link
            class="link"
            href="ueber-uns"
          >
            {{ $i18n('startpage.more_infos') }}
          </b-link>
        </p>
      </b-col>
    </b-row>
    <b-row class="ml-3 ml-md-5 mr-5 mt-5">
      <b-col
        xl="4"
        class="text-xl-right"
      >
        <h2>{{ $i18n('startpage.this_is_how_it_works') }}</h2>
      </b-col>
      <b-col
        cols="12"
      >
        <b-embed
          type="video"
          aspect="16by9"
          controls
          allowfullscreen
          poster="/img/startpage/Video-1200.png"
        >
          <source
            src="/img/startpage/intro.webm"
            type="video/webm"
          >
          <source
            src="/img/startpage/intro.mp4"
            type="video/mp4"
          >
        </b-embed>
      </b-col>
    </b-row>
    <b-row class="mt-3 ml-3 ml-md-5 mt-5">
      <b-col
        cols="12"
        xl="4"
        class="text-xl-right"
      >
        <h2>{{ $i18n('startpage.call_to_action.title') }}</h2>
      </b-col>
      <b-col
        cols="12"
        xl="4"
        class="ml-xl-5"
      >
        <p>
          {{ $i18n('startpage.call_to_action.text') }}
        </p>
      </b-col>
    </b-row>
    <div class="card text-white pb-5 mt-5">
      <div class="call-to-action-image" />
      <div class="card-img-overlay p-xl-5">
        <b-row class="p-xl-5">
          <b-col
            cols="12"
            xl="4"
            class="mb-md-4"
          >
            <b-card-group
              deck
            >
              <b-card
                bg-variant="bg-white"
                text-variant="dark"
                class="p-4"
              >
                <b-row class="mb-3">
                  <b-col
                    cols="10"
                    md="11"
                    xl="10"
                    class="pl-0"
                  >
                    <h5>{{ $i18n('startpage.member.title') }}</h5>
                  </b-col>
                </b-row>
                <b-card-text>
                  {{ $i18n('startpage.member.text') }}
                  <b-link
                    class="link"
                    href="?page=content&sub=joininfo"
                  >
                    {{ $i18n('startpage.more_infos') }}
                  </b-link>
                </b-card-text>
              </b-card>
            </b-card-group>
          </b-col>
          <b-col
            cols="12"
            xl="4"
            class="mb-md-4"
          >
            <b-card-group
              deck
            >
              <b-card
                bg-variant="bg-white"
                text-variant="dark"
                class="p-4"
              >
                <b-card-text>
                  <h5>{{ $i18n('startpage.private.title') }}</h5>
                </b-card-text>
                <b-card-text>
                  {{ $i18n('startpage.private.text') }}
                </b-card-text>
              </b-card>
            </b-card-group>
          </b-col>
          <b-col
            cols="12"
            xl="4"
          >
            <b-card-group
              deck
            >
              <b-card
                bg-variant="bg-white"
                text-variant="dark"
                class="p-4"
              >
                <b-row class="mb-3">
                  <b-col
                    cols="10"
                    md="11"
                    xl="10"
                    class="pl-0"
                  >
                    <h5>{{ $i18n('startpage.store.title') }}</h5>
                  </b-col>
                </b-row>
                <b-card-text>
                  {{ $i18n('startpage.store.text') }}
                  <b-link
                    class="link"
                    href="fuer-unternehmen"
                  >
                    {{ $i18n('startpage.more_infos') }}
                  </b-link>
                </b-card-text>
              </b-card>
            </b-card-group>
          </b-col>
        </b-row>
      </div>
    </div>
    <b-row class="mt-3 ml-3 ml-md-5 mt-5">
      <b-col
        cols="12"
        xl="3"
        class="text-xl-right"
      >
        <h2>{{ $i18n('startpage.how_we_work.title') }}</h2>
      </b-col>
      <b-col
        cols="12"
        xl="6"
      >
        <p>
          {{ $i18n('startpage.how_we_work.text') }}
        </p>
        <p>
          <b-link
            class="link"
            href="https://devdocs.foodsharing.network"
          >
            {{ $i18n('startpage.more_infos') }}
          </b-link>
        </p>
      </b-col>
    </b-row>
    <div class="card text-white pb-5 mt-5">
      <div class="teaser-map" />
      <div class="card-img-overlay p-xl-5">
        <b-row
          align-h="end"
          class="p-xl-5"
        >
          <b-col
            xl="6"
            cols="12"
          >
            <b-card-group
              deck
            >
              <b-card
                bg-variant="bg-white"
                text-variant="dark"
                class="p-4"
              >
                <b-card-text>
                  <h5>{{ $i18n('startpage.near_you.title') }}</h5>
                </b-card-text>
                <b-row>
                  <b-col
                    cols="12"
                    xl="6"
                    class="pl-0 mb-3"
                  >
                    <b-card-text>
                      {{ $i18n('startpage.near_you.text1.part1') }} <b-link
                        class="link"
                        :href="$url('map')"
                      >
                        {{ $i18n('startpage.near_you.text1.part2') }}
                      </b-link>
                      {{ $i18n('startpage.near_you.text1.part3') }}
                      <b>{{ $i18n('startpage.near_you.text1.part4') }}</b>{{ $i18n('startpage.near_you.text1.part5') }}
                    </b-card-text>
                  </b-col>
                  <b-col
                    cols="12"
                    xl="6"
                    class="pl-0"
                  >
                    <b-card-text>
                      {{ $i18n('startpage.near_you.text2.part1') }} <b>{{ $i18n('startpage.near_you.text2.part2') }}</b>
                      {{ $i18n('startpage.near_you.text2.part3') }}
                    </b-card-text>
                  </b-col>
                </b-row>
                <b-card-text class="pt-4">
                  {{ $i18n('startpage.near_you.text3.part1') }} <b-link
                    class="link"
                    :href="currentCountry"
                  >
                    {{ $i18n('startpage.near_you.text3.part2') }}
                  </b-link>
                </b-card-text>
              </b-card>
            </b-card-group>
          </b-col>
        </b-row>
      </div>
      <div class="map-copyright">
        <span v-html="MapAttribution" />
      </div>
    </div>
    <b-row
      class="ml-md-5"
      align-v="center"
    >
      <b-col
        cols="12"
        md="4"
      >
        <b-row class="mt-2 mt-md-0">
          <b-col><h2>{{ $i18n('startpage.demands.title') }}</h2></b-col>
        </b-row>
        <b-row class="mt-2">
          <b-col>
            {{ $i18n('startpage.demands.text') }}
            <b-link
              class="link"
              href="?page=content&sub=forderungen"
            >
              {{ $i18n('startpage.more_infos') }}
            </b-link>
          </b-col>
        </b-row>
      </b-col>
      <b-col
        cols="12"
        md="8"
        class="mt-3 align-self-end"
      >
        <b-img
          fluid
          src="/img/startpage/picture-forderungen.png"
        />
      </b-col>
    </b-row>
    <b-row
      class="ml-md-5 mt-5"
      align-v="center"
    >
      <b-col
        cols="12"
        md="6"
        class="mb-3"
      >
        <img
          class="card-img teaser-towns"
          src="/img/startpage/foodsharing-staedte.png"
          alt="Card image"
          fluid
        >
      </b-col>
      <b-col
        cols="12"
        md="6"
      >
        <h2>{{ $i18n('startpage.towns.title') }}</h2>
        <br>
        <p>
          {{ $i18n('startpage.towns.text') }}
          <b-link
            class="link"
            href="https://foodsharing-staedte.org"
          >
            {{ $i18n('startpage.more_infos') }}
          </b-link>
        </p>
      </b-col>
    </b-row>
    <div class="card text-white pb-5 mt-5">
      <div class="teaser-donate" />
      <div class="card-img-overlay p-xl-5">
        <b-row class="p-xl-5">
          <b-col
            xl="6"
            cols="12"
          >
            <b-card-group
              deck
            >
              <b-card
                bg-variant="bg-white"
                text-variant="dark"
                class="p-4"
              >
                <b-row class="mb-3">
                  <b-col
                    cols="10"
                    md="11"
                    class="pl-0"
                  >
                    <h5>{{ $i18n('startpage.donate.title') }}</h5>
                  </b-col>
                </b-row>
                <b-card-text>
                  {{ $i18n('startpage.donate.text') }}
                  <a
                    class="link"
                    href="unterstuetzung"
                  >
                    {{ $i18n('startpage.donate.link') }}
                  </a>
                </b-card-text>
              </b-card>
            </b-card-group>
          </b-col>
        </b-row>
      </div>
    </div>
  </b-container>
</template>

<script>
import Fork from './fork'
import { MAP_ATTRIBUTION } from '@/consts'

export default {
  components: {
    Fork,
  },
  props: {
    contentBlock1: { type: String, default: '' },
    contentBlock2: { type: String, default: '' },
    contentBlock3: { type: String, default: '' },
    country: { type: String, default: 'DE' },
  },
  computed: {
    currentCountry () {
      if (this.country === 'AT') {
        return this.$url('communitiesAustria')
      } else if (this.country === 'CH') {
        return this.$url('communitiesSwitzerland')
      } else {
        return this.$url('communitiesGermany')
      }
    },
    contentBlockVisible () {
      return this.contentBlock1 && this.contentBlock2 && this.contentBlock1
    },
    MapAttribution () {
      return MAP_ATTRIBUTION
    },
  },
}

</script>

<style lang="scss" scoped>

.row {
  margin-left: 0px;
  margin-right: 0px;
}

::v-deep .link {
  color: var(--fs-green);
  font-weight: bold;
}

.line {
  border-bottom: 1px solid var(--fs-light-grey);
  width: 90%;
  margin: 0 auto;
}

svg {
  height: auto;
  width: 14em;
}

.embed-responsive {
  max-width: 80em;
  margin: 0 auto;
}

.teaser-towns {
  border-radius: 0px !important;
}

.teaser-background {
  background-color: var(--fs-beige);
  max-width: 805px;
  max-height: 451px;
}

h5 {
  font-family: "Alfa Slab One", serif;
  color: #333333;
  font-size: 1.375rem;
  letter-spacing: 0.05em;
}

::v-deep h4 {
  color: #333333;
  font-size: 1rem;
}

::v-deep h3 {
  color: #333333;
  font-weight: bold;
}

::v-deep h2 {
  font-family: "Alfa Slab One", serif;
  color: #333333;
  font-weight: normal;
  letter-spacing: 0.05em;
}

::v-deep h1 {
  font-family: "Alfa Slab One", serif;
  color: #333333;
  font-weight: normal;
  letter-spacing: 0.05em;
}

/*
Extra small devices (portrait phones, less than 544px)
No media query since this is the default in Bootstrap because it is "mobile first"
*/
::v-deep h1 {font-size:1.6rem;} /*1rem = 16px*/
::v-deep h2 {font-size:1.4rem;} /*1rem = 16px*/
::v-deep h3 {font-size:1rem;} /*1rem = 16px*/

.call-to-action-image {
  background: #fff url(/img/startpage/mach-mit-desktop-544@1.5.png) center center no-repeat;
  height: 47rem;
}

.header-desktop {
  background: #fff url(/img/startpage/header-desktop-544.png) center center no-repeat;
  min-height: 23rem;
}

.teaser-map {
  background: #fff url(/img/startpage/karte-544@1.5.png) center center no-repeat;
  height: 32rem;
}

.teaser-donate {
  background: #fff url(/img/startpage/spenden-544.png) center center no-repeat;
  min-height: 22rem;
}

.map-copyright {
  color: #333333;
  text-align: center;
  z-index: 1;
}
/*
####################################################
M E D I A  Q U E R I E S
####################################################
*/

/*
::::::::::::::::::::::::::::::::::::::::::::::::::::
Bootstrap 4 breakpoints
*/
/* Small devices (landscape phones, 544px and up) */
@media (min-width: 544px) {
  ::v-deep h1 {font-size:1.5rem;} /*1rem = 16px*/
  ::v-deep h2 {font-size:1.35rem;} /*1rem = 16px*/
  ::v-deep h3 {font-size:1.25rem;} /*1rem = 16px*/

  .call-to-action-image {
    background: #fff url(/img/startpage/mach-mit-desktop-786.png) center center no-repeat;
    height: 36rem;
  }

  .header-desktop {
    background: #fff url(/img/startpage/header-desktop-789.png) center center no-repeat;
    min-height: 23rem;
  }

  .teaser-map {
    background: #fff url(/img/startpage/karte-786.png) center center no-repeat;
    height: 32rem;
  }

  .teaser-donate {
    background: #fff url(/img/startpage/spenden-786.png) center center no-repeat;
    min-height: 22rem;
  }
}

/* Medium devices (tablets, 768px and up) The navbar toggle appears at this breakpoint */
@media (min-width: 768px) {
  ::v-deep h1 {font-size:2rem;} /*1rem = 16px*/
  ::v-deep h2 {font-size:1.75rem;} /*1rem = 16px*/
  ::v-deep h3 {font-size:1.5rem;} /*1rem = 16px*/

  .call-to-action-image {
    background: #fff url(/img/startpage/mach-mit-desktop-992@1.5.png) center center no-repeat;
    height: 33.5rem;
  }

  .header-desktop {
    background: #fff url(/img/startpage/header-desktop-992.png) center center no-repeat;
    min-height: 23rem;
  }

  .teaser-map {
    background: #fff url(/img/startpage/karte-992.png) center center;
    height: 25rem;
  }

  .teaser-donate {
    background: #fff url(/img/startpage/spenden-992.png) center center no-repeat;
    max-height: 14rem;
  }

  .map-copyright {
    text-align: right;
  }
}

/* Large devices (desktops, 992px and up) */
@media (min-width: 992px) {
  ::v-deep h1 {font-size:2.5rem;} /*1rem = 16px*/
  ::v-deep h2 {font-size:2rem;} /*1rem = 16px*/
  ::v-deep h3 {font-size:1.75rem;} /*1rem = 16px*/

  .call-to-action-image {
    background: #fff url(/img/startpage/mach-mit-desktop-1200@1.5.png) center center no-repeat;
    height: 30rem;
  }

  .header-desktop {
    background: #fff url(/img/startpage/header-desktop-1200.png) center center no-repeat;
    min-height: 23rem;
  }

  .teaser-map {
    background: #fff url(/img/startpage/karte-1200.png) center center;
    height: 25rem;
  }

  .teaser-donate {
    background: #fff url(/img/startpage/spenden-1200.png) center center no-repeat;
    max-height: 14rem;
  }
}

/* Extra large devices (large desktops, 1200px and up) */
@media (min-width: 1200px) {
  ::v-deep h1 {font-size:2.25rem;} /*1rem = 16px*/
  ::v-deep h2 {font-size:2rem;} /*1rem = 16px*/

  .call-to-action-image {
    background: #fff url(/img/startpage/mach-mit-desktop-1200@2x.png) center center no-repeat;
    height: 25rem;
  }

  .header-desktop {
    background: #fff url(/img/startpage/header-desktop-1200@2x.png) center center no-repeat;
    min-height: 23rem;
  }

  .teaser-map {
    background: #fff url(/img/startpage/karte-1200@2x.png) center center;
    height: 25rem;
  }

  .teaser-donate {
    background: #fff url(/img/startpage/spenden-1200@2x.png) center center no-repeat;
    max-height: 23rem;
  }
}
</style>

<style>
.bootstrap .img-news {
  max-width: 55%;
  height: auto;
}

div#main {
  min-height: unset !important;
  padding-bottom: unset !important;
}
</style>
