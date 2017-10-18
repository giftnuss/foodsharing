# 2017-10-18

## Releases notes

Our first release using the new approach, yay!

This release contains some important structural improvements, perhaps most significantly,
we are now using composer for dependencies where possible, and running on php7.

There are also a good number of smaller, but visible changes to the site, mostly bugfixes, but
a few new features too.

Many many thanks to all the contributors that made this possible (in order of appearance in the changelog):

* @valentin.unicorn
* @nicksellen
* @NerdyProjects
* @k.miklobusec
* @peter.toennies
* @raphaelw
* @tiltec

## Breaking changes

## Features

- Added a list of inactive foodsavers to the foodsavers page !183 @valentin.unicorn
- Ensure PHP7 compatibility and upgrade environment to PHP7 !171 @nicksellen
- Added the changelog into the page and link inside the '?' menu !199 @NerdyProjects

## Bugfixes

## Refactoring

## Dev/Test/CI stuff

# 2017-10-18

## Releases notes

Our first release using the new approach, yay!

This release contains some important structural improvements, perhaps most significantly,
we are now using composer for dependencies where possible, and running on php7.

There are also a good number of smaller, but visible changes to the site, mostly bugfixes, but
a few new features too.

Many many thanks to all the contributors that made this possible (in order of appearance in this list):

@valentin.unicorn
@nicksellen
@NerdyProjects
@k.miklobusec
@peter.toennies
@raphaelw
@tiltec

## Breaking changes

## Features

- Added a list of inactive foodsavers to the foodsavers page !183 @valentin.unicorn
- Ensure PHP7 compatibility and upgrade environment to PHP7 !171 @nicksellen
- Added the changelog into the page and link inside the '?' menu !199 @NerdyProjects
- Added a list of inactive foodsavers to the foodsavers page !183 by @valentin.unicorn
- Ensure PHP7 compatibility and upgrade environment to PHP7 !171 by @nicksellen
- Show current commit in footer as well as use it in sentry if errors occur !153 @NerdyProjects
- Reports list can be sorted by main region of the FS !151 @k.miklobusec @peter.toennies 

## Bugfixes

- Fix multiple warnings/notices regarding accessing undefined variables !192 @NerdyProjects
- Fix spinning apple when in profile/conversation with a user without a profile picture !172 @NerdyProjects
- Move login button in navigation a bit to the right to be always clickable !162 @NerdyProjects
- Set the pages timezone globally to Europe/Berlin to not having to rely on server settings !256 @NerdyProjects
- Foodsharers and unverified Foodsavers are no longer able to create business cards !145 @k.miklobusec @peter.toennies
- Breadcrumb links in forum view are working now. !142 @raphaelw @NerdyProjects
- Lots of corrections in spelling and grammar !140, !118 @peter.toennies
- Consistancy in naming: All uses of "Region" are now called "Bezirk" !141 @peter.toennies
- Alphabetical order in the orga-menu !160 @peter.toennies
- Aproximate time of pickup stays in shop settings !161 @peter.toennies
- Fixed spelling in footer of automatic emails !174 @peter.toennies

## Refactoring

- Remove internal FPDI/FPDF library and use it via composer !186 @NerdyProjects
- Remove internal Html2Text library and use it via composer !185 @NerdyProjects
- Remove internal progressbar library in favor of a composer one !181 @NerdyProjects
- Remove custom autoloader in CLI environment as well !177 @NerdyProjects
- Enable composer autoloader and initial src folder !157 @tiltec

## Dev/Test/CI stuff

- Add maildev to support testing outgoing mails !12 @NerdyProjects @nicksellen
- Change default gender from NULL to other to follow production behaviour !190 @NerdyProjects
- Make php cs fixer output in CI nice !191 @NerdyProjects
- Enforce php cs fixer style checks in CI tests !173 @NerdyProjects
- Add CLI Test suite and ensure commands executed via cron at least exist !176 @NerdyProjects
- Example of git pre-commit hook for codestyle checks. !196 @NerdyProjects
- Fix multiple warnings/notices regarding accessing undefined variables. !192 @NerdyProjects
- Fix spinning apple when in profile/conversation with a user without a profile picture. !172 by @NerdyProjects
- Move login button in navigation a bit to the right to be always clickable. !162 by @NerdyProjects
- Set the pages timezone globally to Europe/Berlin to not having to rely on server settings. !256 by @NerdyProjects
- Foodsharers and unverified Foodsavers are no longer able to create business cards. !145 by @k.miklobusec and @peter.toennies
- Breadcrumb links in forum view are working now. !142 by @raphaelw and @NerdyProjects
- Lots of corrections in spelling and grammar. !140, !118 by @peter.toennies
- Consistancy in naming: All uses of "Region" are now called "Bezirk". !141 by @peter.toennies
- Alphabetical order in the orga-menu. !160 by @peter.toennies
- Aproximate time of pickup stays in shop settings. !161 by @peter.toennies
- Fixed spelling in footer of automatic emails. !174 by @peter.toennies

## Refactoring

- Remove internal FPDI/FPDF library and use it via composer. !186 @NerdyProjects
- Remove internal Html2Text library and use it via composer. !185 @NerdyProjects
- Remove internal progressbar library in favor of a composer one. !181 @NerdyProjects
- Remove custom autoloader in CLI environment as well. !177 @NerdyProjects
- Enable composer autoloader and initial src folder. !157 by @tiltec

## Dev/Test/CI stuff

- Add maildev to support testing outgoing mails. !12 @NerdyProjects @nicksellen
- Change default gender from NULL to other to follow production behaviour. !190 @NerdyProjects
- Make php cs fixer output in CI nice. !191 @NerdyProjects
- Enforce php cs fixer style checks in CI tests. !173 @NerdyProjects
- Add CLI Test suite and ensure commands executed via cron at least exist. !176 @NerdyProjects
- Cache vendor folder by using a distinct volume in CI !182 @NerdyProjects
- Run mkdirs using exec so it works in CI !164 @NerdyProjects
- Test environment allows testing of file downloads as well !165 @NerdyProjects
- Output from failed tests is now collected with the test job. !165 @NerdyProjects
- Increase chat test timeout to 10s !167 @nicksellen
- Add debug tools in dev: Whoops for nice error pages and DebugBar showing sql queries !163 @nicksellen
- reduce number of merge conflicts due to changelog !169 @peter.toennies
- move dev/test Dockerfile's into foodsharing-dev/images to remove build step !175 @nicksellen
- fix chat test timing sensitivity !179 @nicksellen

# The wilderness months

* a bit of this and a bit of that

# 2016-10-10 and before

* see [previous changelog](https://wiki.foodsharing.de/Foodsharing.de_Plattform:_%C3%84nderungshistorie)
