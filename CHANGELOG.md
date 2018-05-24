# Not Released

## Release notes

## Breaking changes

## Features
- decreased distance to "close baskets" from 50 to 30 km. !332 #338 by @peter.toennies
- sort fairtiler list by name. !357 #171 by @k.miklobusec
- Store Managers business card creation for region. Remove country card. !76 by @k.miklobusec
- Registered users need to fill their birthday and be 18+ for data protection and liability reasons. !377 @NerdyProjects
- Remove google analytics !374 @NerdyProjects
- Remove external paypal donate button and host locally !374 @NerdyProjects
- Privacy policy need to be agreed before the page can be used !379 @NerdyProjects
- Privacy notice need to be agreed by store coordinators/ambassadors !381 @NerdyProjects

## Bugfixes
- Removing a user from regions is possible again. !372 #14 @NerdyProjects
- Fix search bar not working on some pages !364 by @NerdyProjects
- Remove info section from foodsaver page, if it is empty !320
- It is possible to contact working groups again. !343 #403 by @peter.toennies @NerdyProjects
- Fix store fetch count shown on map info bubble !265 @alangecker @NerdyProjects
- fixed disabled unsubscription of forum posts for fair-teiler responsibles. !331 #317 by @peter.toennies
- fixed stripping of whitespace on email field for registration #58 @nigeldgreen
- use babel polyfills to support more browsers !359 @nicksellen
- fixed check for allowed attachment types in the mail app. !363 #183 by @peter.toennies
- data privacy : removed foodsaver / ambassador selection from map. #165 by @k.miklobusec
- fixed potential security issue in profile picture uploads. !371 #84 @theolampert

## Refactoring
- complete tidying up of all team related files !321 by @peter.toennies
- replaced the PREFIX keyword in the whole source !339 #421 by peter.toennies
- refactored and cleaned the whole reports module. !335 by @peter.toennies
- add webpack for managing frontend assets. !345 @nicksellen
- use symfony form builder work work group edit page !347 @NerdyProjects
- introduce CropperJS for handling image cropping (work group edit) !347 @NerdyProjects
- configure dependency injection as yaml, not PHP !347 @NerdyProjects

## Dev/Test/CI stuff
# 2018-05-14 Hotfix
- Fetching emails to platform mail addresses is more robust against broken mail dates now. #195

# 2018-03-14 Hotfix for 2018-02-28
- Events can be accepted or denied by non event admins again. !342 #418 by @NerdyProjects

# 2018-03-05
- remove ability for ambassador to add any foodsaver to his/her district !328 #405 by @k.miklobusec and @peter.toennies

# 2018-03-02

## Refactoring
- Database multi-row query methods return empty array instead of false on no result !327 @NerdyProjects
- Cleaned up usage of some configuration constants !326 @NerdyProjects

# 2018-03-01
## Hotfixes 2018-03-04
- Never use PDO Boolean binding to avoid silent insert failures [PDO Bug](https://bugs.php.net/bug.php?id=38546) leading to notification bells for unverified users joining regions missing @NerdyProjects

## Hotfixes 2018-03-02
- Remove broken filemanager from content management system (content, email templates) @NerdyProjects
- Fix preview for mass mailer @NerdyProjects

## Dev/Test/CI stuff
- Use [Deployer](https://deployer.org/) to auto-deploy the foodsharing software

## Bugfixes
- Use modern ddeboer/imap library to fetch emails for internal mail system fixing some emails go missing on the way !323 @NerdyProjects
- Events have not been createable/editable due to refactoring mistake @NerdyProjects
- Mumble events can be created again #315 @NerdyProjects

## Features
- Addresspicker: Street/house number editable again, better description for address search @NerdyProjects

# 2018-02-28

## Release notes

Wuhay, this is the first release after our hackweek. Quite a lot has happened:
Nearly 4000 lines of code have been changed, half of the files have been moved into
a better structure and all pages are now served from a [twig](https://twig.symfony.com/doc/2.x/) base template.
As a side change, we now run the latest PHP 7.2.2 and are updating more and more internals to more recent technologies
as well as modern coding techniques.

## Features
- Use of bcrypt as hashing algorithm to store passwords
- Added fairteiler to be shown by default on the map for not registered users and foodsharers !319 by @valentin.unicorn
- Removed the working groups from the team->teammember page !262 @BassTii
- Changed way of gendering in passport from "/" to "_" !251 @D0nPiano
- auto adding of CH-BOTs, Vienna-BIEBs, and ZH-BIEBs to their working groups. !271 by @peter.toennies
- Renamed footer "Unterstützung" to "Spenden" !273 @BassTii
- Updates fullpage.js to 2.9.5 for fixing scrolling in firefox, general smoothness !244 @NerdyProjects
- Page with list of communities for Austria/Germany/Switzerland. !286 by @k.miklobusec
- Single appointment can be set to "appointment cancelled" (=0 Slots) !372 by @k.miklobusec
- Changed the Store address format to not have a separate house number !294 @NerdyProjects


## Bugfixes
- Remove partly broken store coordinator management from store edit page (should happen with "manage team") !283 @NerdyProjects
- Allow using more HTML tags in email templates to not break layout !278 @NerdyProjects
- Reduce size of static images by lossless recompression with trimage !245 @NerdyProjects
- Change impressum to match current association status @NerdyProjects
- Remove mass mail recipient options that are ambigous/irrelevant @NerdyProjects
- Fix missing newsletter unsubscription links for pre-2014 foodsharing.de accounts @NerdyProjects
- Fix newsletter should only be sent to activated accounts @NerdyProjects
- Fixed a bug which throwed an error during mail change
- Show regions in alphabetical order in the region selector (Bezirk beitreten) !267 by @alangecker
- changed old foodsharing „Freiwilligenplattform“ mailfooter for outgoing replies via mail, which was based on lebensmittelretten !287 @irgendwer
- consistent use of jumper list (Springerliste) all over the page. !293 by @peter.toennies
- fixed new fairteiler can not get a region set !294 @NerdyProjects
- fixed ambassador of other region could edit fairteiler !294 @NerdyProjects

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
- Make some components ready to be used without global data passing variable !294 @NerdyProjects
- Introduce Request and Response object and used it in WorkGroupControl !294 @NerdyProjects
- Introduce input deserializer/sanitizer/validator component in WorkGroupControl !294 @NerdyProjects
- Extract genSearchIndex to a service class !294 @NerdyProjects

## Dev/Test/CI stuff

- Improve `FoodsaverVerifyUnverifyHistoryCept` test !279 @tiltec
- Reduce flakyness of acceptance tests further !290 @tiltec
- Disable xdebug in CI to increase test speed !290 @tiltec
- Retry failed tests in CI !290 @tiltec
- Enable [smartWait](https://codeception.com/docs/03-AcceptanceTests#SmartWait) for acceptance tests !279 @tiltec
- Enable xdebug remote debugging for development !276 @NerdyProjects
- Add better seed data for use during development !263 @tiltec
- Enable xdebug profiler for dev environment !296 @NerdyProjects
- Use PHP7.2.2 in dev/test/ci to make it ready for production !301 @NerdyProjects
- More tests for FairTeiler and WorkGroup pages !294 @NerdyProjects

## Other

- PHP 7.2 compatibility of the code !301 @NerdyProjects
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
