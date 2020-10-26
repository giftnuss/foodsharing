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
            <b-card-title class="mt-0">
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
        <h2>Neuigkeiten</h2>
      </b-col>
      <b-col
        cols="12"
        xl="4"
        md="6"
        class="mt-md-5"
      >
        <b-row class="mt-2 mt-md-0">
          <b-col>
            <h3>Online Hackweekend</h3>
          </b-col>
        </b-row>
        <b-row class="mt-2">
          <b-col>
            Sei dabei wenn wir foodsharing gemeinsam besser machen. Dieses Wochenende ist für alle - nicht nur für Softwareentwickler*innen,
            Tester*innen, Designer*innen, sondern auch für Leute vom Support, der Öffentlichkeitsarbeit, der Bildungsarbeit etc.
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
          src="/img/MG_87011.png"
        />
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
      <div class="card-img-overlay">
        <b-row class="m-xl-5">
          <b-col
            cols="12"
            xl="4"
            class="pb-2"
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
                  <h3>{{ $i18n('startpage.member.title') }}</h3>
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
            class="pb-2"
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
                  <h3>{{ $i18n('startpage.private.title') }}</h3>
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
                  <h3>{{ $i18n('startpage.store.title') }}</h3>
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
      <div class="card-img-overlay">
        <b-row align-h="end">
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
                  <h3>{{ $i18n('startpage.near_you.title') }}</h3>
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
        class="card-img teaser-map"
        src="/img/donate-background.png"
        alt="Card image"
        fluid
      >
      <div class="card-img-overlay">
        <b-row align-h="start">
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
                  <h3>{{ $i18n('startpage.donate.title') }}</h3>
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

.call-to-action-image {
  border-radius: 0px !important;
  min-height: 30rem;
}

.teaser-map {
  border-radius: 0px !important;
  min-height: 30rem;
}

.teaser-background {
  background-color: var(--fs-beige);
  max-width: 805px;
  max-height: 451px;
}

h3 {
  font-family: "Alfa Slab One", serif;
  color: #333333;
  font-weight: bold;
  font-size: 1.375rem
}

h2 {
  font-family: "Alfa Slab One", serif;
  color: #333333;
  font-weight: normal;
  font-size: 2.25rem;
}
</style>
