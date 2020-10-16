<template>
  <div>
    <div class="head ui-widget-header ui-corner-top">
      Dein Abholkalendar
    </div>
    <div class="ui-widget ui-widget-content corner-bottom margin-bottom ui-padding">
      <p>
        Du kannst Deinen Abholkalender auch mit einem Kalenderprogramm Deiner Wahl ansehen. Abonniere Dir dazu folgenden Kalender!

        Hinweis: Halte den Link unbedingt geheim! Er enthält einen Schlüssel, um ohne Passwort auf Deinen Account zuzugreifen.

        Hinweis: Dein Kalenderprogramm muss den Kalender regelmäßig neu synchronisieren. Nur dann tauchen neue Abholtermine auf!
      </p>

      <h3>ICS/ICAL/WebCal:</h3>
      <p class="webcal">
        <strong>
          <a
            :href="url + '&opts=s'"
          >
            {{ url }}&opts=s
          </a>
        </strong>
      </p>
      <p class="webcal">
        <strong>
          <a
            :href="url + '&opts=se'"
          >
            {{ url }}&opts=se (inkl. Events)
          </a>
        </strong>
      </p>
    </div>
  </div>
</template>

<script>

export default {
  props: {
    url: {
      type: String,
      default: ''
    }
  }
}
</script>

<style lang="scss" scoped>
@media (max-width: 396px) {
  .webcal {
    word-break: break-all;
  }
}
</style>
