[English short version of this file.](https://devdocs.foodsharing.network/contributing.html)

# Was du hier findest:
* [Wie du unserem Projekt beitrittst,](#wie-du-unserem-projekt-beitrittst)
* [wie du einen Starter Task findest,](#wie-du-einen-starter-task-findest)
* [Tutorial-Empfehlungen für den Umgang mit Git,](#tutorials)
* [auf welchen Ebenen Git funktioniert,](#auf-welchen-ebenen-git-funktioniert)
* [wie ein Workflow aussehen kann](#workflow), (TL/DR: Branch erstellen -> ein oder mehrere commits machen -> pushen -> MR erstellen)
* [was es zum Merge Request (MR) zu sagen gibt,](#was-es-zum-merge-request-zu-sagen-gibt)
* [Wie du selbst einen Rebase machst](#wie-du-selbst-einen-rebase-machst)
* [Wie du mit Rebase-Konflikten in unseren Abhängigkeiten umgehst](#wie-du-mit-rebase-konflikten-in-unseren-abhängigkeiten-umgehst)
* [wie dein MR schließlich bestätigt und am Ende zum Master hinzugefügt wird.](#merge-in-den-master)
* [Weiteres zu issues](#ein-issue-anlegen)
* [Weiteres zu Tests](#testen)

## Wie du unserem Projekt beitrittst
In der foodsharing-IT organisieren wir unsere Arbeit über slack (slackin.yunity.org). Dafür musst du dir ein Konto bei Slack mit einer funktionierenden Mailadresse anlegen.Im Kanal #foodsharing-dev (auf slackin.yunity.org) besprechen wir, was zu tun ist. Sag da am besten auf englisch Hallo und stell dich dem Team vor - mit deinen Skills und woran du gern arbeiten würdest.

Der gesamte Code liegt im Repository https://gitlab.com/foodsharing-dev/foodsharing. Da gibt es neben der "Project ID: 1454647" die Möglichkeit, auf "request Access" zu klicken. Sag uns in [yunity slack](https://slackin.yunity.org/) im Kanal #foodsharing-dev Bescheid und wir geben dir die Bearbeitungsrechte.

Als Mitglied auf Gitlab kannst du
 * Zweige innerhalb des Repositorys Erstellen und Verschieben (außer Master, mehr zu *branches* unten)
 * vertrauliche issues sehen
 * Labels vergeben (super nützlich!)
 * dich issues zuordnen (um anderen zu sagen, dass sie nicht damit anfangen müssen)

Zum Mitarbeiten brauchst du dann noch deine lokale Programmierumgebung (https://devdocs.foodsharing.network/getting-the-code.html).

## Wie du einen Starter Task findest

Mit welcher Aufgabe du anfangen könntest, hängt natürlich von den Vorkenntnisse ab. Frag am besten in Slack nach, was ein guter starter task wäre.

Du kannst natürlich auch selbst etwas aussuchen (https://gitlab.com/foodsharing-dev/foodsharing/issues, gib als Label "starter task" an).

Ordne dich bitte dem Issue zu, das du bearbeitest. (rechts oben im issue: assign yourself)

## Tutorials
Tutorial-Empfehlungen für den Umgang mit Git

* Für die basics: http://rogerdudler.github.io/git-guide/
* Für Rebases etc.: http://think-like-a-git.net, https://git-scm.com/doc und https://learngitbranching.js.org/

## Auf welchen Ebenen Git funktioniert

**Ebene eins**: Deine Arbeitsumgebung. Wenn du Sachen änderst, wird erstmal nix hochgeladen.

Sobald du anfängst, Änderungen zu schreiben, empfiehlt es sich, einen *Branch* zu erstellen.

Wie bei einem Baum ist der Zweig etwas, was von dem Stamm abgeht. Alle haben den Master, aber erstmal hast nur du deinen *Branch*.

https://confluence.atlassian.com/bitbucket/branching-a-repository-223217999.html

**Ebene zwei**: Du möchtest, dass auch andere deinen Branch sehen und darüber philosophieren können. Dafür musst du deine Änderungen bestätigen, *to commit*.

Damit hebst du die Dateien einzeln auf die Upload-Ebene. Sobald du anfängst, zu committen, lohnt es sich einen *Merge Request* zu erstellen.

Den Merge Request erstellst du im Gitlab selbst: https://gitlab.com/foodsharing-dev/foodsharing-android/-/merge_requests/new

Der MR sagt aus: "Ich arbeite daran und werde in absehbarer Zeit fertig". Solange es noch ein *Work in Progress* ist, solltest du den MR auch mit WIP am Anfang benennen.-> https://devdocs.foodsharing.network/contributing.html

Sobald der MR fertig ist, benennst du ihn um, bittest im Kanal um ein review, und wenn jemand gesagt hat: Yo, läuft, dann kann der Merge Request gemerged werden, also mit dem Master zusammengeführt werden. Dafür musst du in der Regel erst deinen Zweig lokal mit dem Master zusammenführen: *rebase*.

Wenn ein MR übernommen ist, hat dein Code **ebene Drei** erreicht und alle, die sich das repository ziehen, haben ihn ebenfalls im Master. 

**Ebene vier** ist dann, wenn von der Test/Beta-Version in die produktive Version rübergeschaufelt wird.

Wenn du Probleme hast, frag am besten jemand im Kanal, ob er/sie dir helfen kann, die Git-Kommandos in der richtigen Reihenfolge auszuführen.

## Workflow
Ein Workflow kann so aussehen:
TL/DR: Branch erstellen -> ein oder mehrere commits machen -> pushen -> MR erstellen

```
git checkout master
git pull
git checkout -b 123-fix-for-issue-123
```

(Erstellt den Branch 123-fix-for-issue-123. Es ist gut, wenn du die Issue-Zahl vorne an deinen Branch setzt, damit wir sehen können, wozu er gehört. Du kannst gerne genauer beschreiben, worum es geht: `git checkout -b 812-1-make-devdocs-contributing-a-german-text`)

Anschließend an Dateien arbeiten.

`git add src/EditedFile.php`

(Fügt die bearbeitete Datei EditedFile.php zu denen hinzu, die hochgeladen werden sollen. Es ist empfehlenswert, `git add -p path_to*_*file` zu benutzen, damit du genau weißt, was du zum Commit hinzufügst.)

`git status`
ODER
`git diff --staged`

(Nützlich, um sich vor dem commit anzuschauen was gerade der Status ist oder was man gerade in seiner staging area hat und was genau zu dem aktuellen commit hinzugefügt wird)

`./scripts/fix-codestyle-local` (oder, wenn das nicht funktioniert, benutz das langsamere `./scripts/fix`)
Damit überprüfst du deinen Code style.

`./scripts/test`
Damit lässt du lokal die Tests durchlaufen. Für spätere Wiederholungstests nutze: `./scripts/test-rerun` (viel schneller!)

```
git commit -m "Changed something in EditedFile.php"
git push --set-upstream origin 123-fix-for-issue-123
```

(Bündelt die Änderungen mit der öffentlichen Notiz "..." und lädt sie hoch. Es kann sein, dass ein Kommando vorschlägt, das noch zu geben ist.

Insbesondere bietet es sich hier an, einen MR für den branch zu erstellen und ihn als Work in Progress (WIP) zu markieren.)

Anschließend weiterarbeiten

```
git add src/AnotherFile.php
git commit -m "Changed something in AnotherFile.php"
git push
```

// This works now// 

Wenn du das Changelog bearbeitet hast:

```
git add CHANGELOG.md
git commit -m "Updated Changelog"
git push
```

(Nach git push kann man als Kommando `-o ci.skip` hinzufügen. Das überspringt Tests, was etwas Energie spart. Tests sind am Ende allerdings wichtig, wenn alle Dateien fertig sind.)

Wenn du fertig bist:

```
git checkout master
git pull
git checkout 123-fix-for-issue-123
git rebase master
// Depening of the age of your branch and other changes, this will just work or require some more work (follow instructions by git)

git push -f 
// force push will overwrite changes on origin, since rebasing changed the history of your branch
```

## Was es zum Merge Request zu sagen gibt

Je übersichtlicher und besser beschrieben der Merge Request (MR) ist, desto einfach ist es für andere, ihn zu reviewen. Das heißt:

* Gib dem MR einen aussagekräftigen Namen, verwende bitte ein Template für die Beschreibung ("default" aus der dropdown-Liste) und beschreibe, was die Änderung macht.

* Wenn es eine Änderung an der UI ist, füg gerne auch ein paar Screenshots ein.

* Geh einmal die Checklist (ganz unten im Template) durch und schau, ob du alle Punkte beachtet hast, bzw. Punkte nicht zutreffen. Beispielweise ist nicht immer ein Test notwendig oder möglich. Die Ausnahme ist der letzte Punkte ("Once your MR has been merged...") der das Testen nach dem mergen betrifft (s.u.)

* Gib dem MR noch ein paar Label, die ihn einordnen. Insbesondere ein "state"-Label hilft zu sehen, ob der MR fertig ist.

Unten im MR kann dieser diskutiert werden. Anmerkungen am Code kannst du am besten unter "Changes" direkt an der entsprechenden Zeile einfügen.

## Wie du einen Rebase machst

1. Du holst Dir mit ```git checkout master``` und ```git pull``` die aktuellen Änderungen vom master Branch.
2. Wechsele danach wieder mit ```git checkout BRANCHNAME``` in deinen branch.
3. ```git rebase master``` ist der Befehl deiner Wahl. (Den Unterschied zwischen rebase und merge findest du hier: https://git-scm.com/book/en/v2/Git-Branching-Rebasing)
	
Falls du das Programm phpstorm nutzt, klickst du rechts unten auf deinen Branchnamen. Dadurch geht ein Menü auf, in dem du den Master auswählst und anklickst: "checkout and rebase onto current".
	
4. Sollte das rebasen zu kompliziert sein oder nicht funktionieren: **Fallen lassen wie eine heiße Kartoffel** ;-) mit dem Befehl ``` "git rebase --abort" ``` Führe stattdessen ein merge durch, da dies einfacher ist, da nur die Änderungen von master branch eingefügt werden. Hierzu kannst du den Befehl 
```git merge master``` anwenden.
	
In phpstorm gibt es zwei Menüpunkte unter dem obigen: "Merge into current". Du findest unten rechts eine Möglichkeit, dir die Versionsunterschiede anzeigen zu lassen. Mit dem Zauberstab-Knopf oben kannst du konfliktfreie Änderungen automatisch vornehmen lassen. "ours" und "theirs" entspricht den Pfeilen am Diff-Rand. (... Hier könnte jemand irgendwann Screenshots einfügen ...)
	
5. Danach kannst du mit einem ```git commit``` und ```git push``` die Änderungen hochladen, wenn du alle Konflikte bereinigt bekommst.
	
## Wie du mit Rebase-Konflikten in unseren Abhängigkeiten umgehst 
	
(https://stackoverrun.com/de/q/11809185)
	
Hast du in deinem Branch Änderungen an der composer.json und / oder client/packages.json durchgeführt und gleichzeitig hat jemand auch an diesen Dateien Änderungen in den master gemergt, kommt es in composer.lock und yarn.lock zu einem Konflikt.
	
	1. Führe den Befehl ```git checkout master -- chat/yarn.lock, client/yarn.lock or composer.lock``` aus
	2. Danach loggst du Dich mit ```./scripts/docker-compose run --rm client sh``` in den Docker-Container "Client" ein.
	3. Führe darin den Befehl ```yarn``` aus und beende mit ```exit```, wenn dieser fertig ist. (bzw. für composer wäre es ```./scripts/composer install```)
	4. Danach kannst du mit einem ```git add chat/yarn.lock, client/yarn.lock or composer.lock``` und ```git rebase --continue``` den Rebase fortsetzen. 
	
(Auf der [Rebase-Seite](rebase.md) gibt es auch ein Beispiel.)


## Merge in den Master

Wenn du fertig bist, meldest du dich im Slack-Dev-Kanal und schreibst da einen Link zu deinem MR hin, mit der bitte um Rückmeldungen. Andere Devs werden sich dann mit Feedback oder Änderungswünschen an Dich wenden. Bitte habe etwas Geduld, wenn das nicht sofort passiert.

Sobald der/die Genehmigende deinen MR für fertig befindet, wird er ihn in den Master übernehmen.

Der Master-Zweig wird automatisch auf beta.foodsharing.de bereitgestellt, wo er getestet werden kann. im Slack-Beta-Kanal solltest du nochmal drauf hinweisen, dass sich etwas geändert hat und getestet werden möge. Dort gibt es dann noch einmal gegebenenfalls Fehlermeldungen. (Besser hier als auf www.foodsharing.de! :-) )

... für einen Überblick über verschiedene Umgebungen: [environments on GitLab](https://gitlab.com/foodsharing-dev/foodsharing/environments) 

Am Ende werden die beta-Änderungen auf die produktiv-Seite übernommen - und dein MR ist abgehakt.

Und dann ... Zeit für eine neue Aufgabe ...?

**Wenn du eine Frage hast, erreichst du uns über [yunity slack](https://slackin.yunity.org/): komm in den Kanal #foodsharing-dev.**

## Ein issue anlegen

Wenn dir etwas an der foodsharing-Webseite aufgefallen ist: prüf bitte, ob das auf beta.foodsharing.de und www.foodsharing.de auftaucht. 
* www und beta = es ist ein unbearbeitetes Verhalten = prüf bitte, ob das Issue bereits bei GitLab eingetragen ist: [issues](https://gitlab.com/foodsharing-dev/foodsharing/issues) ... wenn es noch nicht existiert: **leg das issue bitte mit möglichst genauen Informationen an**
* www und nicht beta = wir haben uns schon drum gekümmert
* nicht www aber beta = wir haben es verursacht = **bitte im Slack-Beta-Kanal melden**

## Testen

Du kannst die Tests mit `./scripts/test` bzw. `./scripts/test-rerun` durchführen (siehe oben). Solange wir die Tests so schreiben, dass sie idempotent ablaufen, nutz bitte *test-rerun*!

Bis jetzt funktionieren die Ende-zu-Ende-Tests (in der Codeception *acceptance test* genannt) gut. Sie laufen mit einem kopflosen Firefox und Selenium innerhalb des Dockers und sie werden auch auf CI-Basis ausgeführt.

Wir sind dabei, den Code umzustrukturieren, um Unit-Tests zu ermöglichen: [incremental refactor](https://gitlab.com/foodsharing-dev/foodsharing/issues/68).

Der während des Testens erzeugte Zustand wird nicht weggeworfen, und du kannst die Test-App nutzen:
[im Browser](http://localhost:28080/), und es hat
[seinen eigenen phpmyadmin](http://localhost:28081/).

Wenn Du die Tests mit eingeschaltetem Debug-Modus durchführen willst, verwende `./Skripte/Test --Debug`.

Wenn Du nur einen Test ausführen willst, gib den Pfad zu diesem Test als Argument an,
z.B: `./Skripten/Tests/Abnahme/LoginCept.php`.

[Ausführlicheres zu Tests findest du hier.](https://devdocs.foodsharing.network/testing.html)
