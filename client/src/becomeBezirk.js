import $ from 'jquery'

import 'jquery-ui'

$(function () {

  // TODO: work out how to manage these twig templating vars :/
  return;

  $("#{{ id }}-notAvail").hide();

  $("#{{ id }}-btna").button().click(function () {

    $("#{{ id }}-btna").fadeOut(200, function () {
      $("#{{ id }}-notAvail").fadeIn();
    });
  });

  $("#{{ id }}-button").button().click(function () {
    if (parseInt($("#{{ id }}").val()) > 0) {

      part = $("#{{ id }}").val().split(":");

      if (part[1] == 5) {
        pulseError('Das ist ein Bundesland. Wähle bitte eine Stadt, eine Region oder einen Bezirk aus!');
        return false;
      }
      else if (part[1] == 6) {
        pulseError('Das ist ein Land. Wähle bitte eine Stadt, eine Region oder einen Bezirk aus!');
        return false;
      }
      else if (part[1] == 8) {
        pulseError('Das ist eine Großstadt. Wähle bitte eine Stadt, eine Region oder einen Bezirk aus!');
        return false;
      }
      else if (part[1] == 1 || part[1] == 9 || part[1] == 2 || part[1] == 3) {
        bid = part[0];
        showLoader();

        neu = "";
        if ($("#{{ id }}-neu").val() != "{{ swapMsg }}") {
          neu = $("#{{ id }}-neu").val();
        }
        $.ajax({
          dataType: "json",
          url: "xhr.php?f=becomeBezirk",
          data: "b=" + bid + "&new=" + neu,
          success: function (data) {
            if (data.status == 1) {
              goTo("/?page=relogin&url=" + encodeURIComponent("/?page=bezirk&bid=" + $("#{{ id }}").val()));
              $.fancybox.close();
            }
            if (data.script != undefined) {
              $.globalEval(data.script);
            }
          },
          complete: function () {
            hideLoader();
          }
        });
      }
      else {
        pulseError('In diesen Bezirk kannst Du Dich nicht eintragen.');
        return false;
      }
    }
    else {
      pulseError('<p><strong>Du musst eine Auswahl treffen.</strong></p><p>Gibt es Deine Stadt, Deinen Bezirk oder Deine Region noch nicht, dann triff die passende übergeordnete Auswahl</p><p>(also für Köln-Ehrenfeld z. B. Köln)</p><p>und schreibe die Region, welche neu angelegt werden soll, in das Feld unten!</p>');
    }
  });
});
