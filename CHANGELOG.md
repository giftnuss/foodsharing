# Unreleased

## Release notes

## Breaking changes

## Features
- Removed the working groups from the team->teammember page !262 @BassTii
- Changed way of gendering in passport from "/" to "_" !251 @D0nPiano
- auto adding of CH-BOTs and ZH-BIEBs to their working groups. !271 by @peter.toennies
- Renamed footer "Unterstützung" to "Spenden" !273 @BassTii
- Updates fullpage.js to 2.9.5 for fixing scrolling in firefox, general smoothness !244 @NerdyProjects

## Bugfixes
- Allow using more HTML tags in email templates to not break layout !278 @NerdyProjects
- Reduce size of static images by lossless recompression with trimage !245 @NerdyProjects
- Change impressum to match current association status @NerdyProjects
- Remove mass mail recipient options that are ambigous/irrelevant @NerdyProjects
- Fix missing newsletter unsubscription links for pre-2014 foodsharing.de accounts @NerdyProjects
- Fix newsletter should only be sent to activated accounts @NerdyProjects
- Show regions in alphabetical order in the region selector (Bezirk beitreten) !267 by @alangecker
- changed old foodsharing „Freiwilligenplattform“ mailfooter for outgoing replies via mail, which was based on lebensmittelretten !287 @irgendwer

## Refactoring
- Consolidate remaining functions and modules !269 @NerdyProjects
- Remove old user registration code !246 @NerdyProjects
- Add initial gateway database classes using PDO !264 @nicksellen
- Add insert/update/delete PDO helper methods !285 @tiltec
- Implement FairTeiler and Region gateway classes !285  @tiltec @nicksellen
- Add Symfony dependency injection container !264 @nicksellen
- Remove unused fpdf font data files !253 @NerdyProjects
- Add twig templating engine !284 @nicksellen
- Add twig templating for main menu and other things !292 @nicksellen
- Remove global usage of Func, DB and ViewUtils Helper classes !289 @NerdyProjects
- Refactor router for HTML controller classes !289 @NerdyProjects

## Dev/Test/CI stuff

- Improve `FoodsaverVerifyUnverifyHistoryCept` test !279 @tiltec
- Reduce flakyness of acceptance tests further !290 @tiltec
- Disable xdebug in CI to increase test speed !290 @tiltec
- Retry failed tests in CI !290 @tiltec
- Enable [smartWait](https://codeception.com/docs/03-AcceptanceTests#SmartWait) for acceptance tests !279 @tiltec
- Enable xdebug remote debugging for development !276 @NerdyProjects
- Add better seed data for use during development !263 @tiltec

## Other

- Added caching for DI container !299 @nicksellen

# 2017-12-11

## Release notes

Happy Birthday, foodsharing!

This release brings a new landing page as well as more spelling and grammar fixes.

Otherwise, it removes some non-working UI elements.

Thanks to all contributors who made this release possible (in alphabetical order):

* @inktrap
* @NerdyProjects
* @nicksellen
* @peter.toennies
* @thinkround
* @TimFoe
* @valentin.unicorn

## Breaking changes

## Features
- When users are added to a region or a working group, their ID is shown next to their name. !214 @NerdyProjects
- Upgraded fontawesome to version 4.7, adding 196 available icons !227 @thinkround
- New landing page implemented along with a restructuring of the navigation bar !221 @thinkround

## Bugfixes

- Fix namespace error introduced in !220 that made image uploads fail !226 @NerdyProjects
- Also show working groups that don't have an email set !226 @NerdyProjects
- Fix bug introduced in !220 !223 @NerdyProjects
- Fix newsletter opt-in during signup !207 @NerdyProjects
- Moved the list of inactive Foodsavers of !183 from the left to the right, because it was impractical in larger regions. !194 @valentin.unicorn
- More consistent use of SI units. !204 @peter.toennies
- Corrected certain errors in spelling, phrasing, and grammar for all pages treating our stores. !208 @peter.toennies
- Same for statistics. !211 @peter.toennies
- fixed the wrong order of foodsaver counts in the lost region list. !187 @peter.toennies
- It is now possible to create correct passports for Orga members. !217 @peter.toennies
- Removed not working store creation button and map view selector from the list of stores. !188 peter.toennies

## Refactoring

- Move more libraries into PSR4 compliant paths and namespaces !220 @NerdyProjects
- Harden routing by adding a table lookup layer to get class view from module name !209 @NerdyProjects
- Move all remaining modules into PSR4 compliant Modules structure !209 @NerdyProjects
- Prepare module loader for PSR4 compliant paths, starting with app/content being moved !206 @NerdyProjects
- Move app/Core module into PSR4 compliant Modules/Core structure !168 @nicksellen @NerdyProjects

## Dev/Test/CI stuff

- Script to help working with email bounces !231 @NerdyProjects
- Test posting to a working group wall !226 @NerdyProjects
- Test uploading profile pictures !226 @NerdyProjects
- Fixed a relict that would only allow using scripts/stop for dev containers !225 @NerdyProjects
- Changed maildev port to 18084, (18083 is used by virtualbox (vboxwebsrv)) !218 @inktrap
- Added support to collect code coverage statistics in codeception !222 @NerdyProjects
- Changed maildev port to 18084, (18083 is used by virtualbox (vboxwebsrv)) !304 @inktrap
- Use cleanly populated database between each test !210 @NerdyProjects
- Add vagrant docker-compose dev environment option !195 @TimFoe @nicksellen

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
* @alangecker

## Breaking changes

## Features

- Added the changelog into the page and link inside the '?' menu !199 @NerdyProjects
- Added a list of inactive foodsavers to the foodsavers page !183 @valentin.unicorn
- Ensure PHP7 compatibility and upgrade environment to PHP7 !171 @nicksellen
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
- Remove bananas when a user gets deleted

## Refactoring

- Remove internal FPDI/FPDF library and use it via composer !186 @NerdyProjects
- Remove internal Html2Text library and use it via composer !185 @NerdyProjects
- Remove internal progressbar library in favor of a composer one !181 @NerdyProjects
- Remove custom autoloader in CLI environment as well !177 @NerdyProjects
- Enable composer autoloader and initial src folder !157 @tiltec

## Dev/Test/CI stuff

- Add maildev to support testing outgoing mails !12 @NerdyProjects @nicksellen
- Example of git pre-commit hook for codestyle checks. !196 @NerdyProjects
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
