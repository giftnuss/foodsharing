# Unreleased

## Features
- Updated tinysort to v3 @peter.toennies
- Added link from names in chatbox title to profiles #100 !614 @colomar
- You can now call a BIEB via the lower info box of a store with just one click !609 @jofranz
- Changelog page now supports links to issues with a # symbol followed by a number like it has been for merge requests before !622 @jofranz
- update htmlpurifier to version 2 !594 @peter.toennies
- prepare support for separated email domain for platform mailboxes
- add security headers !633 @nicksellen
- changed tile maps to wikimedia !639 @alex.simm
- Use typeahead-address-photon for address autocomplete. Update leaflet and typeahead for recent NPM versions in the same go !640 @NerdyProjects

## Bugfixes
- Improve/correct user profile badge count !612 @pmayd
- Datepicker for fetch slots in stores increased to fit 6 week rows #417 !621 @peter.reutlingen
- Changed color of the breadcrumb sitenavigation below the topbar !636 @peter.reutlingen
- List of conversations no longer contains empty conversations #420 !619 @pmayd
- Support falsy (0, '') values for variables in client side translations !641 @NerdyProjects

## Refactoring
- updated jquery to v3 !631 @peter.toennies
- Removed scrollbars from event view !608 @jofranz
- Restructured / cleaned up CSS for Boostrap / Topbar, leaving Bootstrap defaults where possible !616 @colomar

## Dev/Test/CI stuff
- Updated tap-spec in chat to version 5 (fixes vulnerability of lodash) !606 @peter.toennies
- Updated url to 0.11 and tape-spec to version 5 !590 @peter.toennies
- Updated several dev packages: vue eslint parser to v4, eslint plugin vue to v5, css loader to v2 @peter.toennies
- Updated vue-eslint-parser to version 5 and file loader to version 3 !613 @peter.toennies
- Upgrade node to 10.15 and yarn to 1.12.3 !624 @nicksellen
- Remove foodsharing light and API containers in dev setup !624  @nicksellen
- Fix "too many layers" docker issue for influxdb container in CI !624 @nicksellen
- Make client eslint actually fail on error !625 @nicksellen
- Fix a few eslint reported errors !625 @nicksellen
- Add phpstan !634 @nicksellen

# 2019-01-19 Hotfix

- prepare support for separated email domain for platform mailboxes !630 @NerdyProjects

# 2019-01-13 Hotfix

- Use google places session based autocompletion for geocoding to cope with API usage quota !628 @NerdyProjects
- Changed color and typo for the tiny sitenavigation below the topbar !636 @peter.reutlingen

# 2018-12-24

We are happy to release our next version of the foodsharing homepage today. The most beautiful part of that release is 
not its content but the fact that we have input from 15 different developers. The team is growing steadily, which is 
really nice. 

You will find some new features regarding store pages and food baskets but we have also fixed a big bunch of bugs, 
updated and cleaned out a lot of stuff, worked on the interface for our upcoming apps and put some work into the mailing
system of ours. We hope you will enjoy it. 

Merry Christmas :-)

## Features
- shows hint in food basket about public information #373 !570 @k.miklobusec
- Fix conversation name when changing store (name) #294 !508 @surrim
- Notify user when bell notifications arrive without need for page reload #328 !542 @janopae
- Fix read/unread behavior for bell notifications #328 !542 @janopae
- New non-festival homepage !546 @michi-zuri
- Only set session cookie when logged in !544 @nicksellen
- Added a second line to hover texts in stores #88 !547 @alex.simm
- New selection for expiration of baskets #340 !553 @alex.simm
- Making the "tpl_msg_to_team" canceling message more informative !545 @jofranz
- Possibility to edit food baskets #342 !549 @annaos
- Updated to JQuery 2.2.x !572 @peter.toennies
- The possibility to automatically ask for new regions has been removed #329 !571 @peter.toennies
- it is now possible to open profiles in new tabs via middle mouse button !574 @peter.toennies
- Database functions can now be called with critera that contain arrays !559 @janopae
- Added a Rest controller for food baskets #345 !557 @alex.simm
- Allow platform mailing system to work with multiple email domains !583 @NerdyProjects
- Changes MessageRestController limit and offsets to use query parameters !587 @theolampert
- Hight adjustments of "next fetches" in store #376 !601 @jofranz

## Bugfixes
- Foodsavers list is now sorted by name and doesn't reshuffle !578 #54 @odedNea
- Orga members are now able to leave stores they are not responsible for #283 !524 @annaos
- check user permissions in quickreply() in RegionXhr !499 #85 @annaos
- fix exception if request have no "msg"-Attribute in quickreply() in WallPostXhr !499 @annaos
- removed not needed add store button from the dashboard !523 @peter.toennies
- limit conversations sent to client at page loading !542 @janopae
- check permissions before saving a wallpost in WallpostXhr !542 @janopae
- stat_fetchrate is calculated correctly and shown in profile added tvalue in select !598 #281 @k.miklobusec
- fix mail sending by passing instance of Mem to AsyncMail constructor !551 @nicksellen
- fixed wrong html formatting in quick replies to forum posts !534 @peter.toennies
- fixed index check in BasketGateway #354 !556 @alex.simm
- removed fallback for the add date for members in stores  #361 !562 @alex.simm
- show correct date in bells for pickups for more than one date !575 #337 by @mirka-henninger
- fixed statistic box on profile page overlapping on small screens @D0nPiano
- Fixed bug in pickuphistory not showing the end date if it's today. Now it also shows past pickups from pickups happend today !577 @jofranz
- Adding pictures to blog posts and Fairteiler work again !581 @NerdyProjects
- Redirect after joining a new region works again !581 @NerdyProjects
- Bell notifications for store fetch confirmations and for new fairteilers are now generated and stored in the database like normal ones #353 !559 @janopae
- Update store bells via new cron command instead on every bell retrieval !610 @NerdyProjects
- More stability for internal email system as emails are handled like external ones now !583 @NerdyProjects
- Fixed safari issue with the navbar !603 @theolampert

## Refactoring
- Unify Symfony DI configuration !544 @nicksellen
- Add comments in scripts for quicker start of new developers !563 @flukx
- Minor refactoring of control flow all over the source !554 and !555 @peter.toennies
- refactored EmailTemplateAdminGateway from model to gateway !482 #9 @peter.toennies

## Dev/Test/CI stuff
- Add php extensions as composer dependencies, upgrade codeception !558 @nicksellen
- Updated several npm packages (most of them for ci) !564 !565 @peter.toennies
- Use webpack-dev-server instead of webpack-serve !582 @NerdyProjects
- updated webpack and switched to terser !584 @peter.toennies
- Updated whatwg-fetch to version 3 !585 @peter.toennies
- Gather statistics about incoming and outgoing emails !583 @NerdyProjects
- Updated sebastian/diff to version 3 (and phpunit to version 7.3.5) !591 @peter.toennies

# 2018-08-19 Hotfix

- Use Ctrl+Enter instead Shift+Enter for sending messages

## Bugfixes 
- Fix an issue with the navbar for users of safari 11 !527 @theolampert
- Return 404 instead of 500 or broken layout for non existant pages !525 @NerdyProjects

# 2018-08-18

A spontaneous hack-weekend led to us finally finishing this release.
The new topbar is the main feature, paired with a lot of bugfixes of all the things we broke with the last release and the new topbar.
Another big thing to note is that we accidentally removed support for a lot of browsers with the last release which should have been fixed again:
IE11, Safari and slightly older androids should work again, although I can only ask you to please always keep your devices and browsers up to date!

## Features
- new topbar in vue.js !451, #11, #15, #155, #158, #30, #66, #67, #93 @alangecker
- reactive vue stores !451 @alangecker
- resizeable avatar component !451 @alangecker
- updated landingpage with festival content !462 and !471 @michi-zuri
- Only accepted store members see updates on dashboard !412 @k.miklobusec
- Add description about markdown formatting in forum posts !496 @NerdyProjects
- introduce new font fontawesome 5.2 !500 @peter.toennies
- added placeholder text for the birth date in the registration form !505 @peter.toennies
- Search in navbar shows more results, distinct results page removed as it was the same !515 #315 @NerdyProjects @theolampert

## Bugfixes
- Changed button to return to profile on the profile editing page !492 #285 @leisinger.sebastian
- Add missing tagedit lib on mailbox edit page !459 #248 @nicksellen
- reenabling source maps on the production build !468 #254 @alangecker
- removed dead login button and updated registration info for food baskets #240 !457 @michi-zuri
- saving mumble events is now possible !478 #276 @wapplications
- Remove broken LoginXhr->login method !465 @tiltec
- Added possibility to change main region to a part of town (region type 9) !470 #268 @peter.toennies
- fetching parent regions without any given region ID is not possible anymore !474 #258 @peter.toennies
- Fix #287 allowing all members of a group to edit that group !487 @NerdyProjects
- Fix #286 making group applications work again !489 @NerdyProjects
- Fix #255 do not improperly render html tags in region side nav !489 @NerdyProjects
- Fix Database commit missing after migrations in dev/test environment !489 @NerdyProjects
- We were losing some emails because subjects contained new lines, filter this now !491 @NerdyProjects
- Fix forum moderation for unverified users / certain regions !490 @NerdyProjects
- Remove bootstrap tooltip class from profile pictures in banana view !493 @NerdyProjects
- Wallpost pictures are displayed again #279 !497 @NerdyProjects
- Move babel config into webpack config to avoid loading errors !494 @NerdyProjects
- Add fetch polyfill to support ie11 !494 @NerdyProjects
- fix wrong usage of region ID lists for post permissions !503 #308 @peter.toennies
- Fix fairteiler/blog picture upload by exposing necessary javascript methods #307 @NerdyProjects
- Admins of Workgroups are called admins again instead of ambassadors !513 #264 @NerdyProjects
- Do not rely on $\_SERVER['HTTP\_HOST'] being set #263 !510 @NerdyProjects
- Admins of workgroups are called admins again instead of ambassadors !513 #264 @NerdyProjects
- Map legend now more usable in mobile view !215 #119 @michi-zuri
- Fix joining regions from subpages like profile not possible !509 #300 @NerdyProjects
- Fixed `Invalid Date`-Error on safari !469 @alangecker
- Reimplement forum scroll to post functionality !514 #270 @NerdyProjects
- Add back redirect to invalid login event !516 @theolampert
- Reformatting of conversation message times happen in API to avoid javascript error on empty conversation !517 @NerdyProjects @theolampert
- Groups in the menu are also keyboard navigatable !515 #314 @theolampert @NerdyProjects
- Enable autofill username/password for login !515 @theolampert @NerdyProjects
- Fix display of avatars for users without avatars !520 @theolampert @NerdyProjects

## Refactoring
- removed global $g_body_class variable !451 @alangecker
- removed copy of email sending method for CLI applications !464 @NerdyProjects
- refactored statistics from model to gateway !476 #9 @peter.toennies
- removed several layers of the legacy database-classes structure !477 @peter.toennies
- refactored event from model to gateway !478 #9 @wapplications
- removed several deprecated functions from func all over the source !436 @peter.toennies
- refactored content from model to gateway !481 #9 @peter.toennies
- refactored NewArea module from model to gateway !484 #9 @peter.toennies
- refactored index from model to gateway !480 #9 @peter.toennies
- alfa slab one font now used as npm package !501 @peter.toennies
- octicons font not used anymore !504 @peter.toennies and @michi-zuri

## Dev/Test/CI stuff
- Add test for workgroup application / acceptance process !489 @NerdyProjects
- Increase deployer task timeout for more reliable deployments @NerdyProjects
- Add test for forum post creation / moderation / activation !490 @NerdyProjects
- Also lint js/vue files deep inside client/src !520 @theolampert @NerdyProjects

# 2018-07-22 Hotfix
- Fix links to group application details

# 2018-07-21 Hotfix
- Fix foodsaver_id access in StatsControl
- Remove broken login popup

# 2018-07-20 Hotfix
- Fairteiler Walls can be accessed again
- Login Form from Fairteiler removed
- Store name for pickup team notification was missing in serverData
- Deletion of non-existing post lead to 500 instead of 404
- Store statistics could not be updated due to a mistake while refactoring
- Dashboard updates used to show some HTML tags in different entries
- Message notifications have not been sent for some hours

# 2018-07-19
We are quite good at doing major releases every three months.
So here we go:
- Enjoy a new shiny forum post view, using a modern implementation in vue.js / bootstrap-vue
- We now require a javascript enabled browser, as we are using more and more modern frontend technologies
- Forum posts will finally not only allow you to use `whatever <you> want to type «»äá<>>>< in there, but also styling using *markdown*`. See [Wikipedia: Markdown](https://en.wikipedia.org/wiki/Markdown) for an introduction on how to use that
- Behind the scenes, we achieved a lot more, that you hopefully don't notice. See the list below for all changes.

You can read a bit more about the recent weeks and happenings of the developers in the [Development Blog: Summer hackweek](https://devblog.foodsharing.de/2018/07/16/summer-hackweek.html).

Many thanks to @peter.toennies @NerdyProjects @alangecker @theolampert @nicksellen @EmiliaPaz @michi-zuri @tiltec (in order of appearance in this changelog) for all their work done for this release.

## Features
- updated fpdi plugin to v2.0.2 !351 #168 by @peter.toennies
- update symfony to 4.1.0 as well as other dependencies !351 @NerdyProjects
- remove user list in forums to allow big regions to work !421 @NerdyProjects
- add php intl component for localized internationalization !421 @NerdyProjects
- add vue.js, bootstrap & scss !430 @alangecker
- new store list with filtering !430 #191 @alangecker
- implement Wallpost API to replace XHR soon !439 @NerdyProjects
- add HTMLPurifier for proper user HTML handling to be used soon !445 @NerdyProjects
- Forum rest api !442 @NerdyProjects
- Reimplement forum thread/post view as a vue component !442 @alangecker
- forum emoji reactions !442 @alangecker
- Vue functions for i18n and date !442 @alangecker
- Proper input sanitizing for forum posts with support for markdown markup !442 @NerdyProjects
- Properly sanitize outgoing HTML mails !442 @NerdyProjects
- All outgoing emails now generate their plain text via HTML2Text !442 @NerdyProjects
- Show Report ID in Detail Report window #246 @k.miklobusec
- updated wording in respect to new report handling procedure !454 @peter.toennies

## Bugfixes
- removed XSS-possibility in xhr_out method. !370 @theolampert
- Fix pickup slots !390 #215 @nicksellen
- fixed wrong gendering of AMBs in region view and profile view. !386 #214 @peter.toennies
- Added a format placeholder to date input #217 @theolampert
- reduced the height of store info popups by removing the warning frame. !388 #216 @peter.toennies
- The notification for quiz comments is now for the Bots of the quiz team only. !367 #107 by @peter.toennies
- fixed wrong usage of gateway in API. !400 @peter.toennies
- fixed missalignment in future-pickups list. !389 # 136 @EmiliaPaz
- Regaining support for mobile Safari 10 !396 #221 @michi-zuri
- fix relative loading of some xhr/other urls !422 @nicksellen
- fixes user autocomplete fetching for conversation creation
- fix profile sleeping hat variable #243
- fix bug in region dynatree loading #244 !444 @nicksellen
- Only show forum post removal button when the user is allowed to delete a post !456 @NerdyProjects

## Refactoring
- Extract StoreUser module javascript !358 @nicksellen
- refactored and cleaned the whole activity module. !352 by @peter.toennies
- refactored and cleaned the whole API module. !368 #9 by @peter.toennies
- refactored Basket to use gateway. !399 @peter.toennies
- refactored Bell to use gateway. !402 by @peter.toennies
- refactored BusinessCard to use gateway. !406 @peter.toennies
- refactored Buddy to use gateway. !405 @peter.toennies
- removed SQL injection possibilities from all existing gateways !398 @peter.toennies
- refactored Application to use gateway. !397 #9 @peter.toennies
- reduced size of DataBase classes !409 @peter.toennies
- refactored login and registration !403 @theolampert
- partial refactor of Basket module !426 @nicksellen
- refactored region module into twig/webpack loaded javascript !421 @NerdyProjects
- add constants class database constants in region module !413 @peter.toennies @nicksellen
- refactor Model.php and ManualDB.php to gateway classes !420 !424 !425 @tiltec
- refactored tablesorter @alangecker
- use instance access for Session !433 @nicksellen
- refactor Map module to webpack !435 @nicksellen
- all entrypoints load most JS/CSS via webpack now !432 @NerdyProjects
- Refactor forum logic to Gateway/Service/PermissionService !442 @NerdyProjects
- Refactor reactions to be more forum specific !456 @NerdyProjects

## Dev/Test/CI stuff
- Fix cache clearing during test/deploy !414 @nicksellen
- Add testing for client js !422 @nicksellen
- Improve linting config !431 @nicksellen
- Add ./scripts/dev for running webpack dev env !437 @nicksellen
- Improve linting config more (add vue linting) !441 @nicksellen
- Implement basic dev docs content, make shinier readme with contributors !443 @nicksellen
- Add tests for SanitizerService !456 @NerdyProjects

# 2018-05-24

## Hotfixes
- fixed region selector (using webpack now). !383 #207 @peter.toennies
- fix new store page !373 #12 @nicksellen
- export chat/betrieb js functions globally !384 #211 @nicksellen

## Release notes
This release is mostly pushed by GDPR (German: DSGVO) as this forces us to do some changes.
I am not sure yet, if I am positive or negative about that...

Also, we introduce new frontend technology here. I hope we did not break too much :-)

## Features
- decreased distance to "close baskets" from 50 to 30 km. !332 #338 by @peter.toennies
- show date and comment of sleeping hat on profile page. !427 #178 by k.miklobusec
- show home district on profile page. !427 #237 by k.miklobusec
- sort fairtiler list by name. !357 #171 by @k.miklobusec
- Store Managers business card creation for region. Remove country card. !76 by @k.miklobusec
- Registered users need to fill their birthday and be 18+ for data protection and liability reasons. !377 @NerdyProjects
- Remove google analytics !374 @NerdyProjects
- Remove external paypal donate button and host locally !374 @NerdyProjects
- Privacy policy need to be agreed before the page can be used !379 @NerdyProjects
- Privacy notice need to be agreed by store coordinators/ambassadors !381 @NerdyProjects
- quiz comments are now visible for the BOTs of the quiz team only and not for the oga team. !367 #107 by @peter.toennies
- The notification for quiz comments is now for the Bots of the quiz team only. !367 #107 by @peter.toennies

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
- updated fpdi plugin to v2.0.2 !351 #168 by @peter.toennies

## Refactoring
- complete tidying up of all team related files !321 by @peter.toennies
- replaced the PREFIX keyword in the whole source !339 #421 by peter.toennies
- refactored and cleaned the whole reports module. !335 by @peter.toennies
- add webpack for managing frontend assets. !345 @nicksellen
- use symfony form builder work work group edit page !347 @NerdyProjects
- introduce CropperJS for handling image cropping (work group edit) !347 @NerdyProjects
- configure dependency injection as yaml, not PHP !347 @NerdyProjects
- refactored and cleaned the whole activity module. !352 by @peter.toennies

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
- phone number validatino removed from login. Mobile Phone instead of landline phone asked. !361 by @k.miklobusec

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
