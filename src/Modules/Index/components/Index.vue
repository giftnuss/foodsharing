<template>
  <b-container
    fluid
    class="bootstrap bg-white"
  >
    <div class="card text-white pb-5">
      <img
        class="card-img teaser-image"
        src="/img/teaser.png"
        alt="Card image"
      >
      <div class="card-img-overlay">
        <b-row class="ml-xl-5 mt-xl-5">
          <b-col
            xl="2"
            class="d-none d-lg-block"
          >
            <Fork />
          </b-col>
          <b-col
            xl="8"
            cols="12"
            class="text-center ml-xl-5 mt-md-5"
          >
            <b-card-title class="mt-4 mt-md-0">
              <h2>{{ $i18n('startpage.welcome_text') }}</h2>
            </b-card-title>
            <b-card-text class="mt-4">
              <h3>{{ $i18n('startpage.slogan') }}</h3>
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
          </b-col>
        </b-row>
      </div>
    </div>
    <b-row>
      <b-col
        cols="12"
        md="12"
        xl="3"
        class="ml-3 ml-xl-5 text-md-left"
      >
        <span v-html="contentBlock1" />
      </b-col>
      <b-col
        cols="12"
        xl="4"
        md="6"
        class="mt-md-5"
      >
        <b-row class="mt-2 mt-md-0">
          <b-col>
            <span v-html="contentBlock2" />
          </b-col>
        </b-row>
      </b-col>
      <b-col
        cols="12"
        md="6"
        xl="4"
        class="mt-3 align-self-end"
      >
        <span v-html="contentBlock3" />
      </b-col>
    </b-row>
    <div class="line pt-3 pt-xl-5" />
    <b-row class="mt-3 ml-5 mt-5">
      <b-col
        cols="12"
        xl="3"
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
      </b-col>
    </b-row>
    <b-row class="ml-5 mr-5 mt-5">
      <b-col
        cols="12"
      >
        <b-embed
          type="video"
          aspect="16by9"
          controls
          allowfullscreen
          poster="/img/intro.png"
        >
          <source
            src="/img/intro.webm"
            type="video/webm"
          >
          <source
            src="/img/intro.mp4"
            type="video/mp4"
          >
        </b-embed>
      </b-col>
    </b-row>
    <b-row class="mt-3 ml-5 mt-5">
      <b-col
        cols="12"
        xl="3"
      >
        <h2>{{ $i18n('startpage.call_to_action.title') }}</h2>
      </b-col>
      <b-col
        cols="12"
        xl="6"
      >
        <p>
          {{ $i18n('startpage.call_to_action.text') }}
        </p>
      </b-col>
    </b-row>
    <div class="card text-white pb-5 mt-5">
      <img
        class="card-img call-to-action-image"
        src="/img/Rectangle.png"
        alt="Card image"
        fluid
      >
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
                <b-card-text>
                  <h4>{{ $i18n('startpage.member.title') }}</h4>
                </b-card-text>
                <b-card-text>
                  {{ $i18n('startpage.member.text') }}
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
                  <h4>{{ $i18n('startpage.private.title') }}</h4>
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
                <b-card-text>
                  <h4>{{ $i18n('startpage.store.title') }}</h4>
                </b-card-text>
                <b-card-text>
                  {{ $i18n('startpage.store.text') }}
                </b-card-text>
              </b-card>
            </b-card-group>
          </b-col>
        </b-row>
      </div>
    </div>
    <b-row class="mt-3 ml-5 mt-5">
      <b-col
        cols="12"
        xl="3"
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
      </b-col>
    </b-row>
    <div class="card text-white pb-5 mt-5">
      <img
        class="card-img teaser-map"
        src="/img/map.png"
        alt="Card image"
        fluid
      >
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
                  <h4>{{ $i18n('startpage.near_you.title') }}</h4>
                </b-card-text>
                <b-row>
                  <b-col
                    cols="12"
                    xl="6"
                    class="pb-4"
                  >
                    <b-card-text>
                      {{ $i18n('startpage.near_you.text1') }}
                    </b-card-text>
                  </b-col>
                  <b-col
                    cols="12"
                    xl="6"
                  >
                    <b-card-text>
                      {{ $i18n('startpage.near_you.text2') }}
                    </b-card-text>
                  </b-col>
                </b-row>
                <b-card-text class="pt-4">
                  {{ $i18n('startpage.near_you.text3') }}
                </b-card-text>
              </b-card>
            </b-card-group>
          </b-col>
        </b-row>
      </div>
    </div>
    <b-row>
      <b-col
        cols="12"
        md="12"
        xl="3"
        class="ml-3 ml-xl-5 text-md-left"
      >
        <h2>{{ $i18n('startpage.demands.title') }}</h2>
      </b-col>
      <b-col
        cols="12"
        xl="4"
        md="6"
        class="mt-md-5"
      >
        <b-row class="mt-2 mt-md-0">
          <b-col />
        </b-row>
        <b-row class="mt-2">
          <b-col>
            {{ $i18n('startpage.demands.text') }}
          </b-col>
        </b-row>
        <b-row class="mt-2">
          <b-col>
            <b-link
              href="http://test"
            >
              Erfahre mehr
            </b-link>
          </b-col>
        </b-row>
      </b-col>
      <b-col
        cols="12"
        md="6"
        xl="4"
        class="mt-3 align-self-end"
      >
        <b-img
          fluid
          src="/img/forderungen.png"
        />
      </b-col>
    </b-row>
    <div class="card text-white pb-5 mt-5">
      <img
        class="card-img teaser-donate"
        src="/img/donate-background.png"
        alt="Card image"
        fluid
      >
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
                <b-card-text>
                  <h4>{{ $i18n('startpage.donate.title') }}</h4>
                </b-card-text>
                <b-card-text>
                  {{ $i18n('startpage.donate.text') }}
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

export default {
  components: {
    Fork,
  },
  props: { contentBlock1: { type: String, default: '' }, contentBlock2: { type: String, default: '' }, contentBlock3: { type: String, default: '' } },
}

</script>

<style lang="scss" scoped>

.row {
  margin-left: 0px;
  margin-right: 0px;
}

.background-image {
  background-image: url("/img/teaser.png");
  background-repeat: no-repeat;
  background-position-x: center;
}

.line {
  border-bottom: 1px solid var(--fs-dark-grey);
  width: 90%;
  margin: 0 auto;
}

svg {
  height: 15em;
  width: 18em;
}

.embed-responsive {
  max-width: 80em;
  margin: 0 auto;
}

.teaser-image {
  border-radius: 0px !important;
  min-height: 23rem;
}

.teaser-background {
  background-color: var(--fs-beige);
  max-width: 805px;
  max-height: 451px;
}

h4 {
  font-family: "Alfa Slab One", serif;
  color: #333333;
  font-weight: bold;
  font-size: 1.375rem
}

h3 {
  color: #333333;
  font-weight: bold;
}

h2 {
  font-family: "Alfa Slab One", serif;
  color: #333333;
  font-weight: normal;
}

/*
Extra small devices (portrait phones, less than 544px)
No media query since this is the default in Bootstrap because it is "mobile first"
*/
h2 {font-size:1.6rem;} /*1rem = 16px*/
h3 {font-size:1rem;} /*1rem = 16px*/

.call-to-action-image {
  border-radius: 0px !important;
  height: 45rem;
}

.teaser-map {
  border-radius: 0px !important;
  min-height: 33rem;
}

.teaser-donate {
  border-radius: 0px !important;
  min-height: 22rem;
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
  h2 {font-size:1.5rem;} /*1rem = 16px*/
  h3 {font-size:1.25rem;} /*1rem = 16px*/

  .call-to-action-image {
    border-radius: 0px !important;
    height: 36rem;
  }
}

/* Medium devices (tablets, 768px and up) The navbar toggle appears at this breakpoint */
@media (min-width: 768px) {
  h2 {font-size:2rem;} /*1rem = 16px*/
  h3 {font-size:1.5rem;} /*1rem = 16px*/

  .call-to-action-image {
    border-radius: 0px !important;
    height: 33.5rem;
  }

  .teaser-map {
    border-radius: 0px !important;
    min-height: 20rem;
  }

  .teaser-donate {
    border-radius: 0px !important;
    max-height: 14rem;
  }
}

/* Large devices (desktops, 992px and up) */
@media (min-width: 992px) {
  h2 {font-size:2.5rem;} /*1rem = 16px*/
  h3 {font-size:1.75rem;} /*1rem = 16px*/

  .call-to-action-image {
    border-radius: 0px !important;
    height: 30rem;
  }
}

/* Extra large devices (large desktops, 1200px and up) */
@media (min-width: 1200px) {
  h2 {font-size:3rem;} /*1rem = 16px*/

  .call-to-action-image {
    border-radius: 0px !important;
    height: 25rem;
  }

  .teaser-donate {
    border-radius: 0px !important;
    max-height: 23rem;
  }
}
</style>
