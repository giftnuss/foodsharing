# Release "H", unreleased

## Features

## Changes
- Add E-Mail of reported user in overview !2120 @fs_k
- Store-related conversations include a link to their store now !1807 !2134 @ChrisOelmueller

## Bugfixes

## Refactoring
- move even more hardcoded language to language files !2109 @jonathan_b

## Dev/Test/CI stuff
- removed the "request" package !2136 @peter.toennies
- Upgraded deployer to the last version (v7-rc4) !2139 !2140 @\_fridtjof_
- Bump "babel" to reduce vulnerabilities !2137 @peter.toennies
- Bump "mocha" to v9 and "jsdom" to v19 to reduce vulnerabilities !2144 @peter.toennies

# Release "Grapefruit", 2022-01-13

## Features

- Set iCalendar location for exported pickup events #1139 !2072 @iron9
- Display community pin for local foodsharing communities. #53 !2060 !2081 @fs_k 
- Added norwegian translation !2107 @alex.simm
- Select the community pin on a map !2102 @alex.simm 

## Changes
- Allow admins of the working group "Redaktion" to edit blog posts #34 !2061 @alex.simm
- When using a blacklisted email domain the registration process is blocked #1059 @CarolineFischer
- "New event" page requires login !2058 @Buntelrus
- Add a button to the group member list for removing members !2073 @alex.simm @chriswalg
- Add a management mode to group member list to add and remove members !2075 !2119 @chriswalg
- Add frontend functionality to display 'today' or 'tomorrow' in the event header instead of date !2069 #1120 @alexander-sav
- change german wordings in translate file !2080 @Jonathan_B
- Replace picture upload for blog posts with the new upload API #45 !2089 @alex.simm
- adds information on what gets deleted with the user profile !2106 @jonathan_b

## Bugfixes
- Fixed the list of responsible store members in REST responses !2033 #1124 @alex.simm
- Fixed for content edit !2062 @fs_k
- Fix for store change !2065 @fs_k
- Fix for future timeslot visibility to use Europe/Berlin timezone !2070 @fs_k 
- Unverified users who change their home region are not deverified again !2093 @alex.simm
- Some checks for PHP data types in order to avoid Sentry errors !2001 !2099 @alex.simm

## Refactoring
- move more hardcoded language to language files !2108 !2122 @jonathan_b
- Get Members in MemberList.vue from Rest Api !2094 !2111 !2123 @chriswalg @alex.simm
- Refactored the format of the quick search index !2115 @alex.simm 
- Get Members in MemberList.vue from Rest Api !2094 @chriswalg @alex.simm

## Dev/Test/CI stuff

- Update codeception to 4.1.22 because of a security fix !2055 @Morgy93
- Enable Gitlab dependency scanning !2056 @\_fridtjof_
- Fix the return values of database functions !2067 #916 @alex.simm
- All REST endpoints are sorted into categories !2079 #1144 @alex.simm
- exchange description for lat: lon: in translations so that it makes sense !2084 @jonathan_b
- Fix docker setup breaking down because of InfluxDB 2 !2086 @\_fridtjof_
- Add triage bot to pipeline !2074 @iron9
- Remove wrong query parameter in the calendar API #1147 @alex.simm
- added crossreferenced infos to devdocs #1149 !2082
- Run tests for websocket server ("chat server") on CI again !2091 @janopae
- exchanged name of active dev in the devdocs !2103 @Jonathan_B
- Fix warning about mkdir in upload controller !2117 @alex.simm

# Release "Feige", 2021-09-24

## Features
- Allow users to remove trust bananas on their own profile #592 !1920 @alex.simm
- Added a button that allows removing users from the email bounce list !1927 @alex.simm
- For unconfirmed pickup slots, explain in the popup text that a store manager still has to confirm it. !1949 @blinry
- Forum threads can now be closed !1851 !1990 !2045 #724 @alex.simm
- Add french and italian translation to language chooser !1964 !2039 @alex.simm
- Introduces Push Notifications for the Android App !1647 !1976 @dthulke
- Improve wording in German texts, to make the language more consistent, clear, and inclusive in some places. !1959 @blinry @Claraaa @alex.simm @fs_k
- Display same-day pickups when confirming to sign into a pickup slot !1827 !2040 @ChrisOelmueller
- Show membership in profile for workgroups !1988 !1996 @chriswalg
- IT-Support Admins can delete bananas upon request over it@foodsharing.network. !2002 @fs_k
- Added a user search field to the store management panel !2007 !2033 @alex.simm
- New calendar API including token management #80 !1719 !2029 !2045 @alex.simm
- Set iCalendar status for exported pickup events !2030 @iron9

## Changes
- Profile storelist now shows store cooperation status !1828 !1935 @ChrisOelmueller @chriswalg
- Added a partners page for foodsharing.at !1931 @alex.simm
- Redesign startpage !1778 !1982 !1991 !1997 !2015 @chriswalg @Morgy93
- Edit Team in stores is no more, functionality moved to team management mode !1810 !1811 @ChrisOelmueller
- Added a link in Footer.vue to our beta testing issues on beta and dev !1961 @chriswalg
- Make the own personal address visible for the logged in user, as it is already for ORGA #994 !1957 @leonja
- Harmonise the order of links to subpages in the header line and on the page of the work group (AG) and region (Bezirk) #1080 !1954 @andreasklumpp1
- New pickupslots availability moved from midnight to actual pickuptime. #1024 @fs_k
- Convert regular slots to manual slots as soon as someone joins an empty slot !1825 @ChrisOelmueller
- Added a title name for social icons and replaced manitu logo in svg format to footer !1985 @chriswalg
- Allow admins of the newsletter group to see the full list of regions !2011 @alex.simm
- Enabled session cookie checkbox in login form and enabled persistent session for 1 day #956 !2013 @Morgy93
- Allow authors of poll to decide if the options will be shown in a random order #975 !1986 @alex.simm
- Notice as a popup in store for the menu item "Edit Team" where this new function is located !2020 @chriswalg
- Let email input field of login page autofocus !2027 @iron9
- Activate autocompletion for login form !2022 @iron9
- Admins of Voting Workgroups are automatically member of a overall voting in praxis workgroup !2038 @fs_k
- User with administrativ orga power are part of the orga koordination group !2038 @fs_k
- Make expected format of input in user settings clearer !2032 @iron9
- translations: changed wording in calendar module !2076 @Jonathan_B

## Bugfixes
- Long links in the location field of events do not overflow the location box
- Add permission checks to REST endpoints !1946 @alex.simm
- Prevent a timeout when creating polls for many users !1893 @alex.simm
- Allow orga to apply for working groups !1953 !1973 #1050 @alex.simm
- Excluded wall post author from store team members notified about new post !1960 @fabian.rudolf
- Business Card generation: show info and cut off the rest if street or plz + city is longer than 49 characters #834 !1489 @treee111
- Fix permissions for content IDs @alex.simm
- Fix link to wiki on the registration page !1992 @alex.simm
- Fixed the link texts in the newsletter email template !1993 @alex.simm
- Render HTML markings in subject line of email teplates #714 !1899 @alex.simm
- Fixed profile badges hidden for foodsharers #1086 !1978 @andreasklumpp1
- Prevent stores to show up multiple times #1063 !1900 @bjarne.schindler
- Fix errors that occur for non-existing password reset keys !2004 @alex.simm
- Text overflow fixed !2008 #1105 #1106 @YertleTurtleGit
- the function FoodsaverGateway:getOrgaTeam now factors in the user role Orga !2038 @fs_k
- Set correct MIME type for attachments which are fetched via IMAP #1092 !2041 @Thylossus  

## Refactoring
- Update documentation: Give more on information on how to post a testing task in the forum
- Reimplement storelist in user profiles in Vue !1828 @ChrisOelmueller
- New look for event header panels and dashboard invitations including the event's region #992 !1717 #1079 !1940 #1075 !1943 @chriswalg @ChrisOelmueller @fs_k
- Use new upload API for profile photos !1916 !1929 !1932 !1933 !1994 @alex.simm
- Request region children for the region picker from a new endpoint !1934 !1949 #823 @alex.simm
- Remove StoreModel remains, introduce DTO for editing stores !1814 @ChrisOelmueller
- Use a new REST endpoint for deleting emails !1979 #798 @alex.simm

## Dev/Test/CI stuff
- Update mdbook to 0.4.7 !1938 @chriwalg
- Update some frontend dependencies !1892 !1939 !1950 @chriswalg
- Fixed restart behavior for some docker containers !1937 @\_fridtjof_
- Update some backend dependencies !1942 @chriswalg
- Update minishlink WebPush to version 6 !1745 @peter.toennies
- Remove obsolete code for picture uploads !1969 @alex.simm
- Update chat dependencies !1962 !1751 @chriswalg
- Added a foodsharing glossary to our dev docs !1936 @chriswalg
- Delete links in docker compose yml files. It seems to work without it and podman does not work with it. !1972 @chriswalg
- Add basic markdown linting features !2016 @Morgy93
- Update betaTestingIssues link !2028 @Morgy93
- Update docs and MR template for latest beta testing guidelines !2019 @Morgy93
- Update sentry-symfony to latest version !2042 !2047 @\_fridtjof_
- Add docs about markdown and documentation in general

# 2021-04-09 Hotfix
- Add permission checks to REST endpoints !1944 @alex.simm

# 2021-03-30 Hotfix

- Change DTOs for dashboard updates to use date objects !1926 @alex.simm

# 2021-03-26 Hotfix

- Fix for assigning workgroup functions !1922 @fs_k

# 2021-03-22 Hotfix

- Fixed a small logic error that lead to unnecessary and invalid requests being made !1918 @\_fridtjof_
- Fix permission problem in region admin module when saving working groups without functions !1917 @alex.simm

# 2021-03-15 Hotfix

- Fix nightly calculation of store statistics !1914

# Release "Erdbeere", 2021-03-14

## Features
- New menu entry for changing the language of the website #1015 !1877 !1880 @alex.simm
- Show icon and tooltip for working groups with extra functions !1846 @alex.simm
- Added a button that allows the creator of a poll to delete it within the first hour #975 !1906 !1912 @alex.simm

## Changes
- New look for mailbox folder list (rewrite in Vue) !1788 @ChrisOelmueller
- New icons for login and join buttons on topbar, public profile and loggedInFixedNav !1849 @TheSoulT
- New Workgroupfunctions for Store coordinator, report, mediation, arbitration, FSMANAGEMENT (without functionality), PR (without functionality), moderation !1841 @fs_k
- Hide map label in topbar only if displayed on mobile or user is a foodsaver !1869 @joanna-gabis
- Added hasConference permission to Type::COUNTRY, Type::FEDERAL_STATE !1844 @chriswalg
- Added LinkedIn and Youtube as social icon in footer !1850 @chriswalg
- Remove active baskets if a user is deleted !1867 #983 @alex.simm
- Report and Mediation button is not visible against foodsharers !1874 @fs_k
- Updated english translations !1875 @kingu @alex.simm
- Redirect to previous page when logging in using the topbar #689 !1887 @alex.simm
- DevDocs enhanced about GOAL-System #1046 !1884 @fs_k
- Bell for reports #1046 !1900 @fs_k
- special workgroup functions can only be edited by global workgroup creation team.
  report admins and arbitration admins can't report each other
  only new reports shown in report list. #1046 !1902
- Regionoptions for profile mediation / report Buttons #1046 !1903
- Allow users to see their own registration date !1907 @alex.simm
- activate all workgroup functions. Restrict certain functions in self-management (report, arbitration, fs-management) #1046 !1909 @fs_k
- Sort list of past polls and allow filtering them #975 !1901 @alex.simm

## Bugfixes
- Fix transmitted choices in checkbox voting !1847 @alex.simm
- Fix frontend validation in new poll form !1854 #975 @alex.simm
- Fix loading of dashboard updates that contain wall posts from deleted users !1858 @alex.simm
- Set correct placeholder in automated response email for unknown email addresses !1866 @alex.simm
- logging when user is removed from a pickup slot from the user profile !1870 @fs_k
- Remove users from every store team and pickup slot on deletion !1872 @alex.simm
- Blank lines in Markdown on the form for editing store information are now also displayed as blank lines !1878 @stepin
- Removed a duplicate and broken submit button from the workgroup application dialog !1889 @\_fridtjof_
- Prevent sending empty quickreplies on the dashboard #944 !1891 @alex.simm
- Fixed a bug where URLs could be broken in some niche cases !1888 @\_fridtjof_
- Use the correct filename for attachments when sending an email !1904 #755 @alex.simm
- Truncate the commit hash for beta version. The commit hash was to long in small resolutions !1905 @chriswalg
- Fix creation of polls to which only ambassadors are invited !1901 @alex.simm

## Refactoring
- Remove unused code from the Flourish library !1789 @\_fridtjof_
- Initial preparations for migrating controllers to Symfony !1784 @\_fridtjof_
- Vue component for release notes, split into 1 file per release !1832 @ChrisOelmueller
- Simplify controller logic for the `sub` query parameter !1845 @\_fridtjof_
- Unify and move workgroup-function handling to own gateway !1730 @ChrisOelmueller
- Made most current translation .yaml weblate compliant !1835 @tinuthir
- Modernize wallpost module initialization !1772 @ChrisOelmueller
- Replace bell identifiers by enum class #987 !1764 @alex.simm
- Replaced inline css style 'clear:both' with corresponding css class !1859 @scarya
- Move map marker requests to a REST endpoint !1863 @alex.simm
- Split pickup-related gateway functionality off of StoreGateway !1800 @ChrisOelmueller
- Use DTO for creating new stores, remove parts of StoreModel !1809 @ChrisOelmueller
- Use more general queries in the nightly stats calculation for stores !1871 #622 #9 @alex.simm


## Dev/Test/CI stuff
- Made ForumPostCest more reliable !1853 !1856 !1857 !1882 @\_fridtjof_
- Added Xdebug config for macOS !1804 #932 @muffelrudaijer
- Several more PHP7 annotations !1760 @ChrisOelmueller
- More annotations and correctness fixes !1761 @ChrisOelmueller
- Bumped some container versions !1865 @\_fridtjof_
- Updated mdbook to 0.4.6 !1876 @chriswalg
- upgrade webpack-cli and webpack-bundle-analyzer to v 4 !1767 @peter.toennies
- Update some frontend dependencies !1842 !1881 !1890 !1892 @chriswalg @\_fridtjof_
- Update some backend dependencies !1885 !1897 @chriswalg
- Fixed Xdebug !1883 @\_fridtjof_
- Improved table cleanup during seed command !1859 @scarya

# 2021-02-08 Hotfix

- Make the order of values in voting options consistent from left (negative) to right (positive) !1868 #975 @alex.simm

# 2021-01-18 Hotfix

- Make the order of values in voting options consistent from left (negative) to right (positive) !1860 !1863 #975 @alex.simm

# Release "Dragonfruit", 2020-12-31

## Features
- Add button that marks emails as unread !1681 @alex.simm
- Implement a Webcal URI for iCal export !1716 @chriswalg @ChrisOelmueller
- Add Button in message and bell list in the topbar to mark all bells and all conversations as readed #935 #1016 !1673 !1725 !1787 TheSoulT
- Store managers can remove old store posts #92 !1690 @ChrisOelmueller
- Allow displaying recent pickup history in profiles !1715 !1763 #989 @ChrisOelmueller
- Given trust bananas will now notify the receiver via Bell !1795 #548 @muffelrudaijer
- Topbar search query for numeric ID now returns profile link for that ID !1783 @ChrisOelmueller

## Changes
- Notify a user who is accepted to a workgroup with a bell #808 !1708 @alex.simm
- Don't set "follow by email" as default when opening a new thread !1676 @Caluera
- New favicon created with support for all platforms and browsers !1720 !1741 @chriswalg
- Notify a user who is accepted to or declined from a workgroup with a bell #808 !1708 !1721 @alex.simm
- Only display broadcast message for logged-in users !1757 @ChrisOelmueller
- Recolor formerly brown topbar to beige !1762 @ChrisOelmueller
- Adjust structure and color scheme of dashboard activity updates !1753 @ChrisOelmueller
- Ambassadors can access management for workgroups in "their" region from topbar #965 !1742 @ChrisOelmueller
- Send chat notifications by mail, only if last login is less than 6 months away !1623 @chriswalg
- Replaced in topbar donation page to twingle pages !1773 @chriswalg
- Complete redesign of the footer !1769 !1805 !1834 @chriswalg
- Allow editing of polls in the first hour !1786 #975 @alex.simm
- Restrict the search in a store's edit team dialog to people from the same region !1812 #1012 @alex.simm
- Rewrite blogpost management list in Vue !1791 @ChrisOelmueller

## Bugfixes
- Resolved the CooperationStatus tooltip "no longer exists" and "No contact yet" in StoreList !1714 @chriswalg
- Markdown support for store posts #921 !1690 @ChrisOelmueller
- Update voting tool functions and usability !1813 !1792 !1726 !1780 !1793 !1829 #975 #1021 @alex.simm @fs_k @ChrisOelmueller
- Fully display hour and minute values when editing pickup times #1001 !1754 @ChrisOelmueller
- Add login checks to all forum endpoints to prevent errors !1739 #993 @alex.simm
- Resolved newsletter flag: Disable the default value to false !1779 @chriswalg
- Resolved the user registration: Removed adding the accepted_date to getPnVersion !1779 @chriswalg
- Disabled session cookie checkbox in login form and, until there is a solution in issue 956
and persistent session disabled after each request, so that LastLogin is set correctly again !1785 @chriswalg
- If Ambassador removes user from District verification is not removed and history is not written !1803 @fs_k
- Fix a possible crash with reactions to deleted forum posts !1797 !1802 @ChrisOelmueller
- Check if mail addresses are verified, allow sending out new verification mails #564 !1334 !1817 !1818 @pfaufisch @chriswalg
- Fix a rare problem with push notification data !1798 @ChrisOelmueller
- Fix javascript error when closing a chatbox before the conversation is loaded !1823 @alex.simm
- Prevent deletion of group/region with stores, subregions, or fairsharepoints !1774 #905 @alex.simm

## Refactoring
- Flatten some workgroup function calls !1702 @ChrisOelmueller
- Remove some long-unused grabInfo code !1695 @ChrisOelmueller
- New endpoints for requesting and setting dashboard activity options !1669 @alex.simm
- Deprecate and remove most of v_activeSwitcher !1697 @ChrisOelmueller
- Deprecate `v_form_submit` !1700 @ChrisOelmueller
- Remove obsolete `v_dialog_button` helper !1693 @ChrisOelmueller
- Deprecate / remove some obsolete view helpers !1693 @ChrisOelmueller
- Deprecate both `v_form_recip_chooser` variants !1699 @ChrisOelmueller
- Reimplement user verification as REST endpoints !1696 @ChrisOelmueller
- Move xhr functions for store requests to REST !1755 @alex.simm
- Replace isOrgaTeam checks with may(orga) #883 !1680 @ChrisOelmueller
- Rewrite store wall with REST and Vue !1690 !1750 @ChrisOelmueller
- Rewrite store application screen in Vue !1756 @ChrisOelmueller
- Some changes to Session and PageHelper group handling !1742 @ChrisOelmueller
- Clean up IndexController and some related code !1775 @\_fridtjof_
- Fix a possible crash with reactions to deleted forum posts !1797 @ChrisOelmueller
- Remove some unused global JS !1766 @ChrisOelmueller
- Rewrite trust banana dialog in REST and Vue !1770 @ChrisOelmueller
- Introduced a session versioning mechanism to avoid having to log out all users in order to refresh them !1820 !1831 @\_fridtjof_
- Migrate dashboard updates to REST and DTO !1767 @ChrisOelmueller

## Dev/Test/CI stuff
- Update many frontend dependencies !1723 @chriswalg
- JS linter now asks for dangling comma !1728 @ChrisOelmueller
- Update backend dependencies !1732 !1740 @chriswalg
- Update Composer to version 2 and PHP to version 7.4.11 !1734 @peter.toennies
- Fixed `rm` and `clean` scripts !1737 @\_fridtjof_
- Increase phpstan analysis level to 5 and fix all issues !1674 !1729 @ChrisOelmueller
- Remove the abandoned deployer/recipes package and deployer/phar-update and update deployer to v 7 !1743 @peter.toennies
- Annotate some variables in unit tests, streamline namespace "imports" !1748 @ChrisOelmueller
- Remove unused `triage` CI script #979 !1750 @ChrisOelmueller
- Update Nelmio API doc bundle to 4.0.1 !1747 @alex.simm
- upgrade webpack-merge to v 5 !1744 @peter.toennies
- Replaced gitbook v1 to mdbook !1738 @chriswalg
- Improved the RegisterCest.php: This check the variants with and without newsletter now. !1779 @chriswalg
- Make build/test CI interruptible when new commits are pushed !1799 @\_fridtjof_
- Fix an incredibly unlikely potential crash when editing a workgroup !1796 @ChrisOelmueller
- Update docker images to node:14.14.0-alpine3.12, redis:6.0.8-alpine and ruby:2.7.2-alpine3.12 !1724 @chriswalg

# 2020-11-25 Hotfix
- Fix the CSP to make the map work again !1776 @\_fridtjof_

# 2020-10-23 Hotfix

## Bugfixes
- Fix komoot's URL for address search !1727 @alex.simm

# Release "Cranberry", 2020-10-11

## Features
- Display deep link to forum posts, better responsive layout #937 !1650 !1652 @ChrisOelmueller
- New store status "permanently closed" #786 !1655 @ChrisOelmueller
- Add voting tool #309 #975 !1633 !1668 !1687 !1691 !1692 @alex.simm
- Add FairSharePoint Function Workgroup #974 !1667 !1679 @fs_k
- Send a message with optional custom text when rejecting or kicking someone from a pickup slot !1677 #595 @alex.simm
- Add ageband to statistic page !1685 @fs_k
- Activity page shows only last entry to buddywall, eventwall, fairsharepointwall !1694 @fs_k
- Leaving a district is captured in history. Leaving a home district removes verification. !1358 @fs_k

## Changes
- Removed FAQ section !1568 @chriswalg
- Disable chat notification by mail for new users. #949 !1620 @chriswalg
- Removed quiz description #737 !1565 @chriswalg
- Removed unused legal agreement for new amb's #821 !1619 @chriswalg
- Allow translating lots and lots of text !1637 !1666 @ChrisOelmueller
- Workgroup Admins of Startpage and Team/Partner page can edit their pages #967 !1651 @fs_k
- Moved guide page (ratgeber) to wiki page #776 !1567 !1664 @chriswalg
- Add store log for different user activities in store #553 !1658 @fs_k
- Orga may 'delete' foodbaskets #51 !1670 @fsk_k
- Display public profile for deleted users as well !1703 @ChrisOelmueller
- fetchrate is not shown as long as reporting system is down !1706 @fs_k
- Sort forum search results by last update !1704 @ChrisOelmueller

## Bugfixes
- Filter outdated baskets from REST responses #706 !1608 @alex.simm
- Removed form for new amb's #821 !1619 @chriswalg
- Fix default center value for leaflet map !1644 @alex.simm
- Exclude author of FoodSharePoint posts from notification emails !1638 #835 @alex.simm
- Try to correct scrolling to requested forum post #930 #968 !1650 @ChrisOelmueller
- Allow adding workgroup admins or members if none exist currently #896 !1637 @ChrisOelmueller
- Fix server error when activating new email address that has already been activated #966 !1664 @alex.simm
- Fix disappearing store traffic light when store name is long !1682 #984 @ChrisOelmueller
- Fix last creation date in pass generation table when creating multiple passes !1684 #686 @alex.simm
- Fix a page crash with unexpected URL parameters !1686 @ChrisOelmueller
- Optimise the creation of many event invitations #958 !1710 @alex.simm

## Refactoring
- Move registration request to the REST endpoint #819 !1632 !1642 @alex.simm
- Renamed unused table fs_basket_has_wallpost and removed related code #889 !1569 @chriswalg
- Rewrite forum thread list in Vue #86 #764 #962 !1650 !1652 @ChrisOelmueller
- Use existing use search endpoint for tagedit autocomplete !1588 @alex.simm
- Move removal of store requests to new REST endpoint !1648 !1683 @alex.simm
- Move buddy requests to REST endpoint !1646 #847 #798 @alex.simm
- Use TranslatorInterface for many old translations !1637 !1655 !1659 !1662 !1663 !1666 !1688 @ChrisOelmueller
- Prepare event invitations for REST !1627 !1657 @ChrisOelmueller
- Refactor some translations from twig to messages.de.yml #824 !1639 @chriswalg @jonathan_b
- Clarify and extract some permission handling !1671 @ChrisOelmueller
- Some PHP linting chores and more type hints !1641 !1654 @ChrisOelmueller
- Move bell XHR requests to REST !1659 @alex.simm
- Introduce some more specific store gateway functionality !1686 @ChrisOelmueller
- Replace Vue filters with prototypes !1689 @ChrisOelmueller
- Remove unused Xhr methods for region applications !1707 @alex.simm
- Deprecate `v_form_picture` !1701 @ChrisOelmueller
- Rename `theme` variables to `thread` !1645 #840 @Caluera @ChrisOelmueller
- Deprecate `v_scroller` !1698 @ChrisOelmueller
- Make navigation bar responsive !1532 !1821 @moffer
- Refactor translations from twig email templates to yml to messages.de.yml !1640 #824 @jonathan_b @chriswalg

## Dev/Test/CI stuff
- Fix banana unit test !1649 !1656 #964 @alex.simm
- Update devdocs for database migrations in phinx, fedora 32 and WSL2 !1675 @chriswalg
- Increase phpstan analysis level to 3 and fix all issues !1654 @ChrisOelmueller
- Increase phpstan analysis level to 4 and fix all issues !1672 @ChrisOelmueller
- Fix `outdated` CI script #979 !1712 @ChrisOelmueller
- Fix `script/clean` not removing test containers !1806 #1027 @muffelrudaijer

# 2020-08-11 Hotfix

## Bugfixes
- Fix removal of non-existing user photos in nightly maintenance !1634 @alex.simm
- Allow seeing basket markers on map without being logged in !1636 @alex.simm

# Release "Birne" (pear), 2020-08-09

## Major changes
- New store-team list !1499 !1591 !1593 !1621 @ChrisOelmueller
- Workgroup Function: Welcome to workgroup #945 !1544 !1612 @fs_k

## Features
- Sort own (managed) stores to top of topbar store list #920 !1546 @ChrisOelmueller
- Allow opening bell notifications in new tab #912 !1540 @ChrisOelmueller
- Added Workgroup Function: Welcome to workgroup !1544 @fs_k
- Add integration with BigBlueButton video conferencing system !1561 @NerdyProjects
- Show an error notification if the user is redirected from a region page to the dashboard !1571 @alex.simm
- Better highlight the position picker input-box !1583 !1586 @ChrisOelmueller
- Removed faq and replaced to external freshdesk support form in top and footer menu #817 !1587 @chriswalg
- Call foodsaver from pickup slot dropdown menu #772 !1591 @ChrisOelmueller
- Hide all phone numbers for unverified store team members and jumpers !1591 @ChrisOelmueller
- Show an popover for the "remember me"-function, if clicked in password field on login page !1585 #370 @TheSoulT
- Add a link to reset the password in the login popover !1585 @TheSoulT
- Store managers can toggle team list mode to sort by last pickup !1593 @ChrisOelmueller
- Public profile page, to allow checking badge validity #688 !1604 @ChrisOelmueller
- Ask whether message draft should be kept when switching between chat conversations !1621 @ChrisOelmueller
- Make titles of forum threads searchable !1609 #99 @alex.simm

## Bugfixes
- Clarify that new forum threads won't reach members inactive for more than six months !1553 ("merging" !1385 and !1233) @zommuter
- Push notifications for group chats no longer sound like the message addresses the user specifically !1574 @\_fridtjof_
- Exclude workgroups from the "my groups" section in the topbar search for which the user was not yet accepted !1589 @alex.simm
- Only count pickups via the function getMyStore until the current day !1599 @chriswalg
- Stop overwriting mailbox names if they contain unread mails #789 !1600 @ChrisOelmueller
- Prevent page from jumping to top when deleting bells !1597 @ChrisOelmueller
- Fix crashing "All my stores" page when user has no home district !1616 #936 @alex.simm
- Fix creation and deletion of buddy bells !1618 #942 @alex.simm
- Fix wrong viewer/session ID on profile page !1629 @alex.simm

## Refactoring
- Move master-update function for regions to the rest controller !1547 @alex.simm
- Add missing endpoint for deleting forum threads !1545 #913 @alex.simm
- Use rest endpoints for basket deletion and the coordinates on the baskets map !1550 @alex.simm
- Remove Magnific Popup by rewriting trust banana UI code to fancybox !1530 !1556 @ChrisOelmueller
- Some refactorings from StoreModel to StoreGateway !1196 !1554 !1558 #9 @svenpascal @alex.simm
- Modernize icon handling of store bells + fairteiler bells #907 !1560 !1566 !1597 @ChrisOelmueller
- Rewrite store team list in Vue !1499 !1591 @ChrisOelmueller
- Increase phpstan analysis level to 2 and fix all issues !1575 @NerdyProjects
- Use Request/Response objects in the application entry points !1576 @\_fridtjof_
- Do not show unsubscribed email subscriptions for forum threads in notification settings #893 !1570 @chriswalg
- Remove and refactor some PHP translations &22 !1583 !1590 @ChrisOelmueller
- Rename Fair-Teiler to Fairteiler #906 !1590 @ChrisOelmueller
- Rename "Service" classes to "Transaction" classes and move them to the corresponding modules @janopae !1475
- Rename "Helper" namespace to "Utility" @janopae !1475
- REST API: file uploads with resizing of images in foodshare points !818 @alangecker
- Removed dependency on old Db class from some classes !1598 #9 @alex.simm
- Move sending of bananas to new REST endpoint !1617 #798 @alex.simm
- Remove jquery contextmenu, refactor some dashboard view code &22 !1606 @ChrisOelmueller
- Refactor profile view, permissions, and pickup schedule overview !1604 @ChrisOelmueller
- Rewrite store pickup history in Vue, using the Pickup components &9 &22 !1611 @ChrisOelmueller
- Move entry points to Symfony controllers @\_fridtjof_ !1602

## Dev/Test/CI stuff
- Include sentry as symfony bundle to hopefully not miss error reporting for Rest API anymore !1562 @NerdyProjects
- Include Phinx database migration tool for (hopefully soon) automated migrations and less confusion about database state !1549 @NerdyProjects
- Install phinx as a separate project in deployment !1584 @NerdyProjects
- made development on Windows possible again by tweaking direcotry cache and line endings !1603 @peter.toennies
- Add emails to seed data !1601 @alex.simm
- Update to Symfony 5 / FOSRestBundle 3 !1573 @NerdyProjects

# 2020-07-15 Hotfix
- Disabled report link on profile page and Xhr functions for sending reports !1610 @alex.simm

# 2020-06-15 Hotfix
- Allow emails for password reset and email address change to be sent with higher priority !1557 #925 @alex.simm
- Gender value for women and man is now fixed !1564 @chriswalg
- Show on profile a warning if the private mail adresse is on bounce list for orga and foodsaver them self. #931 !1572 @chriswalg
- Update devdocs to recommend Docker Desktop for Win10 Home !1578 @\_fridtjof_
- Use gitlab ci services instead of building and running docker images in CI !1577 @NerdyProjects

# 2020-06-01 Hotfix

## Bugfixes
- Allow to accept privacy notice, so people can become store managers again !1551 @NerdyProjects

# 2020-05-18 Hotfix

## Features
- Added tooltips to Dashboard Activities-Overview filter options !1526 @mr-kenhoff

## Bugfixes
- Be more robust against errors in the WebSocket Chat server: Let request suceed anyway. !1525 @NerdyProjects
- Fix crash on incoming email that would generate a bounce !1524 @NerdyProjects
- Fix broken data in internal email system email storage for sender address !1523 @NerdyProjects
- Migrate all broken email storage sender addresses to be valid !1523 @NerdyProjects
- Fix accessing null value as array in FairteilerView. !1527 @NerdyProjects
- Fix wrongly accessing null values in Fairteiler. !1527 @NerdyProjects
- Fix javascript error accessing the map the first time / without localstorage. !1528 @NerdyProjects
- Fix issuing invalid SQL IN() query !1534 @NerdyProjects
- Fix not logged in users getting errors when things should have been logged to their not-existing session !1531 @NerdyProjects
- Fix accessing invalid location for users without a session or without an address. !1538 @NerdyProjects
- Fix Content Security Policy violation for web worker for older browsers (fixes push notification for older browsers) @NerdyProjects
- Wrap long email address in user profile #828 !1541 @ChrisOelmueller

## Refactoring
- Make the instant search in the topbar use a new rest endpoint without legacy wrapping code for search results !1522 !1559 !1579 @alex.simm

## Dev/Test/CI stuff
- Migrate gitlab CI config to use rules instead of only/except !1529 @NerdyProjects
- Do not run CI tests before deployment !1529 @NerdyProjects
- Do not run gitlab dependency scanning job as nobody used the output !1533 @NerdyProjects
- Explain (wanted) php code structure in devdocs !1463 @flukx

# Release "Apfelsine" (orange), 2020-05-16

## Features
- Introduce Web Push Notifications #336 !734 @janopae
- Re-enable pickup slot markers after production release !1331 !1307 @jofranz
- Refactored register form to multi step pages in vue !1099 !1309 !1370 !1401 !1476 @chriswalg @moffer @ChrisOelmueller
- Redirect to login page after login failed !1342 @chriswalg
- Display icon for verified Foodsavers in store popup #766 !1294 @pfaufisch
- update twig to version 3 @peter.toennies
- update bootstrap-vue to version 2.7 #807 !1382 @ctwx_ok @peter.toennies
- Added number of food share points to statistics !1351 #81 @alex.simm
- Switch the tile server from maps.wikimedia.org to MapTiler !1355 @dthulke
- Orgas are now able to delete wallposts for foodshare points !1359 @pfaufisch
- Show internal email address on user's own profile !1386 #465 @alex.simm
- Dashboard updates can be filtered !735 !1424 @D0nPiano @ChrisOelmueller
- Updates from events + foodsharepoints displayed on dashboard !735 !1441 #227 #588 @D0nPiano @ChrisOelmueller
- Picture thumbnails are included in dashboard snippets of wallposts !735 #454 @D0nPiano @ChrisOelmueller
- Link Avatar pictures on dashboard to profiles !735 #464 @D0nPiano @ChrisOelmueller
- Add option for new forum threads in unmoderated fora to send mail or not !1233 #64 @jofranz @Caluera
- Make it possible to unfollow forum bells #271 !1191 !1467 @jofranz @chriswalg @ChrisOelmueller @moffer
- Introduce permissions for user profile data handling: maySeeHistory(), mayAdministrateUserProfile(), mayHandleFoodsaverRegionMenu() and mayDeleteFoodsaverFromRegion() !1288 !1438 @jofranz @alex.simm
- Improve Metrics collection: Log execution timing including database execution timings for all controllers now. !1480 @NerdyProjects
- Release notes introduced and replaced with the changelog !1474 @chriswalg
- Extend the lifetime of persistent sessions after every request !1496 @dthulke
- Include thread title in bell notification for forum posts #869 !1487 @ChrisOelmueller
- Load store menu in the top bar only when the menu is opened in order to make the slot markers release ready !1502 @janopae
- Update all package dependencies to current as of 2020-05-11 !1503 @NerdyProjects
- Allow changing the language for all translation capable texts !1485 @NerdyProjects
- Allow adding people to conversation by putting their ID into the recipient field !1508 @NerdyProjects
- Return less results for the user search when creating a new conversation !1513 @NerdyProjects
  - Now, it returns everybody who is in the same groups as you but excludes people that are just in state or country groups (e.g. Niedersachsen, Deutschland, Europa).
  - Ambassadors additionally get all people in their ambassador regions and subregions
  - Orga and Welcome team get all people, now also including foodsharers
- Massively optimize performance of user search when creating a new conversation !1513 @NerdyProjects

## Bugfixes
- Don't ask to accept the legal requirements when not logged in. #811 !1384 @CarolineFischer
- On Mobile last pickup and member since information is shown on team list in stores #788 !1335 @fs_k
- Date strings on Dashboard are now displayed in correct language #606 !1316 @pfaufisch
- Fixed mails not displaying line breaks !1317 !1344 @pfaufisch
- Improved the banner on welcome page for mobile devices !1329 @chriswalg
- Makes a break with longer words so that e.g. links in the store description don't come across the page #715 !1269 @chriswalg
- Region statistics for ambassadors do not include workgroup admins anymore #778 !1341 @Caluera
- Fix bug preventing publishing, editing and deleting of blog posts !1349 @pfaufisch
- Fix small bug in sending quickreply messages without personal field !1321 !1367 @alex.simm
- Fix WallpostPermissions now deny read access by default #352 !1353 @pfaufisch
- Fixed mailboxes not beeing generated for some users !1356 #705 @kheyer
- Resolve "If name of the district or adress is too long on the business card, this is cut off or goes over the edge" #700 !1362 @chriswalg @kheyer
- Fixed Dashboard to display activity stream after date-fns update !1366 @pfaufisch
- Fix broken bell menu caused by missing date conversion !1364 @dthulke
- Strip whitespaces from email addresses before sending them !1372 #802 @alex.simm
- Fix "Mobile: can't apply to stores, window cut off" #765 !1357 @panschk
- Don't include unconfirmed slots into statistics and fetch history !1360 @caluera
- Fix reapplication not possible after beeing denied once !1277 #767 @chris2up9
- Fix missing region id bug for food share points !1375 @alex.simm
- Fixes crash in the date formatting logic when updating the list of bells !1388 @dthulke
- Fixed bug in email template rendering during when quickreplying to forum threads !1403 @alex.simm
- Links in shortened dashboard updates no longer invalid due to cut-off #691 !735 @D0nPiano @ChrisOelmueller
- Now possible to have many disabled sources of dashboard updates #365 !735 @D0nPiano @ChrisOelmueller
- Fixes the marker loading in the region admin tool !1415 @dthulke
- Adjusted picture sizes of slots, thread posts and of menubasket. !1298 !1423 #735 @moffer
- Fix registration link on login page !1425 #856 @alex.simm
- Submenus of burger menu (mobile view) for example 'Infos' can be scrolled. !1411 #838 #837 @moffer
- Fix link of top-left icon in navbar and make the hover-heart appear more often !1421 #853 @alex.simm
- fixed arrow handling !1408 @jonathan_b
- Show a prompt to select a home district on the dashboard if none is choosen #716 !1123 @lebe1 @dthulke @Caluera
- Make the description clearer for the mail option when opening new thread !1453 @Caluera
- Fix issues introduced with push notifications #831 #841 #857 !1442 !1443 !1444 !1445 !1446 @janopae
- Short Description is shown on profile and purpose of both self descriptions is made clear in settings !1145 #656 @fs_k @Caluera
- Fallback to raster tiles if the browser does not support WebGL !1455 @dthulke
- Correct wording: "Fairteiler" and "FairTeiler" to "Fair-Teiler" in some files #890 !1479 @treee111
- Correct Dashboard preview of ordered + unordered lists #455 !1481 @ChrisOelmueller
- Fix gender selection during registration that was set to 'unselected' by mistake @alex.simm
- TagEdit color correction when hovering #867 !1514 @ChrisOelmueller
- Fixed outgoing mails not displaying line breaks !1317 @pfaufisch

## Refactoring
- Name generation for chat groups has been extracted to an own method method, which is now used by push notifications and in the E-Mail generation for missed chat messages. The new method does a slightly better job at naming; beta testers are welcomed to check the E-Mails generated for missed chat messages. @janopae
- Improve mayEditStore() to fail faster !1311 @jofranz
- Restructure the definition of the Region ID constants. !1325 @theFeiter
- Remove moment.js dependency. !1303 #678 @ctwx_ok
- Moved the button for new stores to vue store list !1282 !1339 @chriswalg
- Refactored wakeupSleepingUsers to MaintenanceGateway !1301 @Caluera
- Removed obsolete jsonp warning code in xhrapp !1319 #777 @alex.simm
- Add function to database class that allows inserting multiple rows !1267 #757 @alex.simm
- Remove Sessions from Gateway-Classes !1314 @panschk
- Exchange nightly not fully working bell update check with the daily/reliable method !1312 @jofranz
- Update date-fns to version 2.9.0 !1042 !1363 !1422 !1447 @chriswalg @ChrisOelmueller
- Moved newsletter test functionality from Xhr to Rest API !1354 @alex.simm
- Removed lost@foodsharing address and added sending a reply email if an address was not found #510 !1346 @alex.simm
- Redesigned the option to delete FS account if not agreeing with privacy policy. !1318 @thefeiter
- Use larger SQL queries for event invitations instead of many small queries !1285 #774 @alex.simm
- redirected the refs from storelist.vue to lang.de.yml !1386 #824 @jonathan_b
- Extended the text in footer for "DoNotReply"-Mails with the information not to reply to the message #826 !1389 @thesoult
- redirected hardcoded German strings from topbar to lang.de.yml !1410 #824 @jonathan_b
- recreate Dashboard update-overview with vue components !735 !1424 @D0nPiano @ChrisOelmueller
- changed ActivityModel to return data without HTML or JS !735 !1424 @D0nPiano @ChrisOelmueller
- New function for deleting store wall posts via rest !1390 #9 @alex.simm
- Get rid of /upload.php !1365 @\_fridtjof_
- Removed unused delPost xhr function !1417 @alex.simm
- Removed ActivityModel by moving functions to ActivityXhr !1434 #9 @alex.simm
- Reimplement footer in vue !1437 @ChrisOelmueller
- redirected the german refs from storestatusicon.vue and pickuplist.vue to lang.de.yml !1392 #824 @jonathan_b @thesoult
- Refactored store infos to vue js !1406 !1477 !1492 @chriswalg @ChrisOelmueller
- Moved profile Rest endpoint to user controller !1374 @alex.simm
- Removed the two deprecated functions from Session.php !1259 @koenvg
- Extend RegionPermissions to a mayAdministrateRegions() method. Removes the topbar menu entry if false !1236 @jofranz
- Update to PHP 7.4.5 and fix some backwards incompatible changes @NerdyProjects
- Add database constraints to fs_faq !1436 @ffm_hessen
- Replace XHR request for baskets in topbar by existing REST endpoint !1472 @alex.simm
- CSS adjustments for foodbasket page, mobile dashboard view with columns !1494 @ChrisOelmueller
- Port WebSocket server ("chat") to TypeScript and refactor it in an object oriented way !1470 @janopae
- Leaving regions is done by new Rest endpoint !1459 @alex.simm
- Replaces hard coded Links in AdminMenu !1510 @mr-kenhoff
- Moved database request for the maintenance script from model to gateway !1394 #9 @alex.simm

## Dev/Test/CI stuff
- Add "linux" tag for finding CI servers !1332 @nicksellen
- fix some doc annotations !1361 @\_fridtjof_
- update mkdirp to version 1 @peter.toennies
- added german contributing guide and english FAQs to devdocs !1376 @Jonathan_B
- updated sentry to version 2 @peter.toennies
- update codeception to version 4, phpunit to version 9, and sebastian/diff to version 4 #1369 @peter.toennies
- Less ports are exposed to the dev computer's network now !1367 @\_fridtjof_
- replace raven by the newest sentry JS SDK @peter.toennies
- update loader-utils to version 2, url-loader to version 4, and file-loader to version 6 @peter.toennies
- improve the statistics for outgoing mail in grafana !1395 #64 @dthulke
- Update sentry javascript SDK from 5.15.2 to 5.15.4 because it was broken. !1402 @chriswalg
- added information in devdocs @jonathan_b
- replace all uses of npm by yarn !1397 @peter.toennies
- improve PHP Database documentation, add new convenience methods and make delete safer !1399 @\_fridtjof_
- Added a workaround to devdocs for fedora 32 or debian 10 and docker !1439 @chriswalg
- added information on our Workflow and how to solve Merge Conflicts in devdocs @jonathan_b
- added text about refactoring to devdocs @Caluera !1464
- added text about releases to devdocs @Caluera !1486
- Changes text for posting test tasks in beta Slack channel @moffer !1471

# 2020-04-22 Hotfix
- Use Geoapify as tile server and use mapbox gl to render vector tiles !1405 @dthulke
- More accurate email rate limiting !1419 @jofranz
- Set height for topbar and removed the height of div#main. Now is the broadcast message completely readable !1383 !1391 !1432 @chriswalg
- Improve the statistics for outgoing mail in grafana !1395 #64 @dthulke
- Fixed rendering error when replying to forum posts !1447 @ChrisOelmueller


# 2020-03-26 Hotfix
- Use WebSocket connection to determine whether a user is online or not !734 @janopae
- Adds a null check to the chat server to avoid null WebSocket messages !1398 @dthulke
* start documenting database tables and columns !1259 @flukx

# 2020-03-16 Hotfix
- Fix nightly fetcher warnings by using expected id instead of betrieb_id allowing all nightly maintenance methods to be executed again #747 !1348 @jofranz
- Limit forum notifications to users logged in last 6 months #64 !1385 @fs_k


# 2020-01-26
Another release from your lovely dev Team. A lot of changes have been done "under the hood" that will help developers with modernization of the codebase and to improve the website further. A lot of old code has been removed, restructured and database access has been improved. Some nightly maintanance have been optimized. A more user friendly overview of the new improvements can be found here: https://foodsharing.de/?page=bezirk&bid=741&sub=forum&tid=98018 accessable for every foodsaver.


## Features
- Adds a proper error messages if users specify their birthday in the wrong format !1114 @dthulke
- Add email shortcut to regions and workgroup side menu !1118 @jofranz
- Add email count to menu shortcut to make it easier for workgroup and region admins to respond to unanswered mails !1124 @jofranz
- Changed slot icons for pending (transparent again) and comfirmed to font awesome !1116 @chriswalg
- Enable pickup-list for foodsavers own profile in profile view which was only visible for ambassadors/"BOTs" before. !1122 @jofranz
- Add amount of foodsavers to in-/active lists in region foodsaver menu !1117 @jofranz
- Add "Termin"/"Date" and bot/amb "forum"/"board" as dashboard post type !1148 @jofranz
- Add foodsaver id to store team search results when manually adding a foodsaver #660 !1150 @jofranz
- Add foodsaver id to search results when starting a new chat #660 !1149 @jofranz
- The number of active basket requests are shown and baskets request can be withdrawn and rejected by the basket provider !1121 #710 @dthulke
- Add fs id to food share point admin management search results #660 !1152 @jofranz
- Warn basket users without location data and inform them why it makes sense to provide those in order to use baskets on the website !1143 @jofranz
- Sort the stores-list by the added-on date as default !1161 @treee111
- Redirect from a wall of regions (e.g. "Deutschland", "Arbeitsgruppen Überregional") to the forum.  Walls only exist for workgroups #750 !1186 @treee111
- Save mail quickreplies to sent folder #611 !1166 @alex.simm
- Filter not cooperating stores ("does not want to cooperate" and "gives to (other) charity") out of dropdown menu list #323 !1144 @jofranz
- Basket rest endpoint returns the list of requests to show them in the app !1169 @dthulke
- Open video on start page in external tab to avoid csp issues #617 !1177 @dthulke
- Improves usability of the topbar using screen readers !1179 @dthulke
- Change "impressum" in newsletter footer to new fs postal address !1205 @jofranz
- Show events on dashboard which started one/more days in the past and are ongoing !1215 @treee111
- Allow to configure site to send CSP headers without a report-uri !1210 @nicksellen
- Increase workgroup application limit numbers !1218 @jofranz
- Show foodsharer id in profile for everyone !1232 @jofranz
- Menu entry for newsletter email sending is only active if mayAdministrateNewsletterEmail() permission is true !1235 @jofranz
- Admins of newsletter workgroup (331) now have access to the newsletter module additional to orga members !1235 !1256 @jofranz
- Show a error message, if changing a mail address failed !1091 @chriswalg
- Add info about limitations of nightly slot warnings !1275 @jofranz
- Send an email to the amb and group workgroups (AGs) if the last admin/amb leaves a workgroup/region !1153 @jofranz
- Updated foodsharing etikette for registration process !1295 @chris2up9
- Refactored and changed time range for store fetch warning mails for store manager to today + tomorrow instead of 15:00 limit !1289 @jofranz

## Bugfixes
- fixed page crash when as ambassador on region -> foodsaver clicking on one foodsaver !1278 @Caluera
- Correct title for map page !1276 @chris2up9
- fixed the jpeg image detection in the flourish library, leading to people not being able to login anymore !1100 @alangecker
- Set initial region in new store form to undefined if it is a larger region or country !1112 #418 @alex.simm
- Removed hidden profile pic in settings !1090 @chriswalg
- Add previously uploaded picture to the edit form for food share points !1136 #727 @alex.simm
- When answering a long e-mail, the send and cancel button disappeared. The buttons moved next to fileupload #404 !1127 @chriswalg
- Automatically relogin after joining work group !1113 #125 @alex.simm
- Disable possibility to show stores for foodsharers #132 !1146 @jofranz
- Fixes SQL query in helper method to delete bells. This may has caused errors when approving slots #712 !1142 @dthulke
- Increase search min length in store and fsp team management list #396 !1151 @jofranz
- Do not initialise ReportList vue component if it is not shown !1159 @dthulke
- Ensures quiz break message after three failures inbetween 30 days #736 !1162 @svenpascal
- Prevent forum thread email sending to countries and federal states !1160 @jofranz
- Prefetchtime is now correctly stored when creating a new store !1170 @dthulke
- Change the close icon in pickup slot message and food basket request form to a better position  #731 !1172 @chriswalg
- Fixed FoodSharePoint deletion problem #642 !1168 @alex.simm
- Show correct message immediately after failing the 5th quiz try #729 !1176 !1313 @svenpascal @chriswalg
- Narrow down permissions to not allow ambassadors calling newsletter sending xhr methods !1197 @jofranz
- Fix database method which prevents newsletter sending #754 !1198 @jofranz
- Improved SQL query which caused that the team of large work groups could not be updated anymore #726 !1199 @dthulke
- Use font awesome icons for store status indicators to avoid that they disappear when the store name is too long #742 !1190 @dthulke
- Avoid duplicate names in user autocomplete !1223 @dthulke
- Fix error when logging out while not logged in !1240 #753 @alex.simm
- Prevent exception for orga users if a deleted user profile is visited @jofranz
- Added missing login check for local reports page. Previously there was an empty table with no data !1238 @jofranz
- Added missing login and permission check for mailbox page, making sure only BIEBs can see the mailbox #771 and #769 !1260 @pfaufisch
- Added missing login and permission check for mailbox page !1260 @pfaufisch
- Remove "Aktionen"-column from list of user stores !1252 @koenvg
- Fix wrong may group use. Admins of EUROPE_REPORT_TEAM (region/workgroup id: 432) now actually have reports permissions on a level with orga !1250 @jofranz
- !1199 fix: Remove group members only from specific group instead of all groups and regions !1258 @jofranz
- Redesign for chatbox and messages page !1265 @chriswalg
- Center basket map on Germany if logged out !1249 #740 @alex.simm
- Show correct from/to information in mailboxes !1264 !1239 #603 @alex.simm
- Fix adding members to mailboxes by orga !1255 !1302 !1308 #677 @alex.simm
- Fix missing translations for MenuBasketsEntry. !1271 #761 @ctwx_ok
- disable delete account buttons for non-orga users !1279 @Caluera
- repaired link to profile in the very first pinwall post #512 !1281 @Caluera
- Disallow foodsharing email addresses to be used as password restore addresses !1268 #744 @alex.simm
- Remove email addresses from the bounce list before sending a confirmation mail !1268 #756 @alex.simm
- Move map control elements on small devices #695 !1286 @lea.mzw
- Move bellupdatetrigger() to maintenance class only executing it nightly. Accidentally fixes the date distance to a unconfirmed slot which was reseted every 5 minutes before !1300 @jofranz
- Made the list of recipients of a mail foldable to avoid unreadable mails !1280 #65 @alex.simm
- Name change of regarding fairsharepoint contact person to "Ansprechpartner" !1305 @fs_k
- Fixed broken tooltips !1304 @ctwx_ok
- Fix error message when downgrading a foodsaver and do only downgrade if user role has decreased !1323 @pfaufisch @jofranz
- Temporarily disable pickup slot markers for production release !1307 @jofranz
- Passportgenerator list sorted by name default, workgroups are not shown anymore !1310 @fs_k
- Fix text overflow problems with events and notifications #722 #876 !1487 @ChrisOelmueller
- Bots can now add up to three new store managers to abandoned stores #209 #405 !1319 @pfaufisch

## Refactoring
- Optimize database access for legal queries !1292 @CarolineFischer
- refactored to use count() instead of more complicated expressions !1273 !1296 @Caluera
- Removed support for old passwords stored in sha1 or md5, since we switched to Argon2 now almost 2 years ago. !1095 @alangecker
- Reduced complexity of the profile module !1037 @peter.toennies
- refactored blog from model to gateway !789 #9 @peter.toennies
- refactored statsman from model to gateway !1111 #9 @peter.toennies
- refactrored the food share point module !1108 !1105 @peter.toennies
- Removed broken nightly bell deletion maintenance script !1180 @dthulke
- Uniform foodsharing colors on the whole page #75 !1174 @chriswalg
- statistic kilo code refactoring !999 @jofranz
- statistic kilo calculation optimized in sql !999 @fs_k
- Removed unused xhr_out method !1208 #132 @alex.simm
- Introduce content id constants for content pages !1200 @jofranz
- Replaced some hardcoded sql with prepared statements !1207 #757 @alex.simm
- Removed some dead code !1213 @svenpascal
- Remove food basket pinboard frontend from !969 entirely !1203 @jofranz
- Refactored database access from controllers to gateways !1192 #9 @alex.simm
- Removed unused clearAbholer() method, which would falsely remove all fetches from a user which need to stay for documentary reason !1216 @jofranz
- Replaced REPLACE INTO queries with prepared statements !1124 #757 @alex.simm
- Renamed "Verschwendungsfasten" to "foodsharing-Städte" !1222 @D0nPiano
- Replace SettingsModel by SettingsGateway !1163 #9 @svenpascal
- Moved mayHandleReports() to ReportPermissions class with deprecation in Session class !1241 @jofranz
- Introduce very basic permissions for FAQ editing. Removes the topbar menu entry if false. For now this only replaces orga permissions in accessing the FAQ admin tool !1245 @jofranz
- Move mayEditQuiz() from session to own permission class with deprecation in Session class. Added some example implementations. !1242 @jofranz
- Introduce permissions for content administration. For now there are no additional permissions given. Removed the entry from the menu if permission is false !1243 @jofranz
- Introduce mayAdministrateBlog() permission in BlogPermissions.php and use it for current permission checks. It rebuilds previous behaviour. Removes the topbar entry from the menu if permission is false !1246 @jofranz
- Introduce mayManageMailboxes() permissions. Removed the entry from the menu if permission is false. !1244 @jofranz
- Introduce store creation permissions and use it for at three different places where a store button is shown. Also use it at before showing store creating page !1237 @jofranz
- Moved database access from RegionXhr, MailboxXhr, and ForumService to gateways !1228 #9 @alex.simm
- Removed Xhr method for posting store wall posts (xhr_addPinPost) and made it part of the REST API (POST on /api/stores/{id}/posts}. !1226 #719 @janopae
- Removed mayLegacy function from session !1248 @alex.simm
- Introduce NewsletterEmailPermissions class for mayAdministrateNewsletterEmail() permission checks !1235 @jofranz
- Removed SQL statements from Session, BasketXhr, and XhrMethods + fix !1261 !1247 #9 @alex.simm
- Switched use of Session::id to Session::may !1257 @pfaufisch
- Refactored FoodsaverModel to FoodsaverGateway !1178 !1266 !1299 #9 @svenpascal
- Updated dependencies and fixed broken templates !1272 !1283 @ctwx_ok
- Removed class IndexGateway as it serves no purpose !1270 #763 @panschk
- Remove unused stats and maintenance methods of nightly stats run !1274 @jofranz
- Refactored region gateway to use more prepared statements !1297 @alex.simm

## Dev/Test/CI stuff

- add dependency scanning GitLab CI configuration !1183 @nicksellen
- adds error infos to the exception of an unpreperable query !1195 @dthulke
- Added docker toolbox download link for windows users in dev docs #733 !1147 @lebe1
- Remove hotUpdateChunkFilename config option workaround !1202 @jofranz @nicksellen
- Fix DebugBar !1212 @nicksellen
- Add scheduled CI job to print outdated dependencies to slack !1221 @nicksellen

# 2019-11-14 Hotfix
- disabled the new report list on region level @peter.toennies @jofranz
- Updated duplicated delete() method to avoid deprecation error crashes in sentry !1141 @jofranz

# 2019-10-08 Hotfix
- nearby baskets on dashboard were missing foodsaver name and creation time @peter.toennies
- remove email and gender from some responses in MessageXhr.php !1098

# 2019-10-06

Long time of silence from the IT, you might think. And yes, the last release is four month ago. But we have been busy all the time.
A lot is getting cleaner in the background and we are continuously improving the connection between our homepage and our two native apps.
And we have even included some new features for you.

## Major changes
- New pick-up list

## Features
- Added Rest endpoint for nearby baskets !875 @alex.simm
- updated bootstrap-vue to v2.0.0-rc28 @peter.toennies
- Added Rest endpoint for the current user's profile !880 @dthulke
- improved the region join selector text #562 @peter.toennies
- Allow subgroups to groups !904 @fs_k
- pickup list includes now stores in sub-districts, year added, divers separated !906 @fs_k
- Added a city-column to the store list table, added row-details on mobile devices, stores now ordered by name #456 !679 @tihar
- Added yellow info box with a warning not to change the address fields. Visible for orga/bot in "edit profile" menu !911 @jofranz
- Added active foodsaver and jumper count to store popup on map !920 #620 @fs_k
- Added yellow info box with "how to use the address picker" and what this data is used for to:
    - profile settings !895 @jofranz
    - event page !915 @jofranz
    - store settings !922 @jofranz
    - fair-share-point settings !1085 @jofranz
- Added average daily fetch count to statistics page !900 @chris2up9
- Use SwiftMailer for outgoing emails !925 @NerdyProjects
- Shake it! Randomly shuffle ambassadors and working group admin's list order to make it harder always to contact the most senior one !924 @jofranz
- Added Rest endpoint for users !916 @alex.simm
- improved description for photo upload in user settings !902 @peter.toennies
- Moved pickup signup logic into API !874 @NerdyProjects
- Rewrite pickup list on store page in vue !874 @alangecker @NerdyProjects
- Removed TOX-ID in foodsaver settings #31 !935 @chriswalg
- Removed twitter and github for Team-Member !944 @chriswalg
- Sorted trust bananas by date #550 !970 @sepulcrum89
- Added a mailto hyperlink for group mail in workgroup #139 !948 @chriswalg
- Added one week as an option for automatic pickup slots in store settings !945 @jofranz
- Added Rest controller and normalization for stores !885 @alex.simm
- new chat design with badges and layout changed !928 @kniggerich
- Disabled new food basket comments while showing pre existing ones for another while during migration period until there are no comments left !969 #534 @jofranz
- Add my own last date of pickup to particular store info box to help stores with pickup rules/limitations !893 @jofranz
- Show last event wall posts on the dashboard update-section for events which got not declined #227 !417 @fs_k @jofranz
- Team list in store view is now collapsed on mobile as it was with pinboard comments already !983 @jofranz
- Added Rest endpoint to edit baskets !992 @alex.simm
- Graz BIEBs automatically added nightly to their working group !987 @peter.toennies
- Added reference to OpenStreetMap to the map attribution #661 !1009 @dthulke
- Added Rest endpoint for fair share points !1012 @dthulke
- Add backend logic for changing basket locations !1021 @alex.simm
- Updated the wording regarding "not more than 2 foodsavers per pickup !1029 @peter.toennies
- Fixed bell notifications for new wallposts in stores !1030 @jofranz
- The map is zoomed out in case no address is specified instead of showing the ocean !1053 @dthulke
- A region's reports are now accessible for the ambassadors in charge via the region menus !1041 @peter.toennies
- Add basket counters to statistics #81 !1045 @chris2up9
- Placed event buttons correctly for mobile on dashboard #640 !1044 @henrikhertler
- Link avatar pics to user profile at report. !1047 @moffer
- Prevent email form from sending mails to "noreply" addresses. Blocked by a warning !1065 @jofranz
- Adding apple-app-site-association file to allow Universal Linking in our possible apps !1082 @rastadapasta

## Bugfixes
- restrict pickup statistic on country level to orga !1073 @fs_k
- Profile button "remove from all slots" is now only enabled for orga !968 #362 @fs_k
- Fixed a bug in MessageModel.php which caused that conversation members were sometimes not returned !878 @dthulke
- Direct links are referring to correct location when using the nav bar login !864 @YasminBucher
- Fixed broken modal for forum post deletion !894 #599 @peter.toennies
- Show "food basket already got picked up" instead of accidentally showing a blank page !891 @jofranz
- Allow users being deleted out of regions #604 @NerdyProjects
- Topbar now static again after related changes in css !907 !897 @jofranz @alangecker
- Fixed wrong ID for reports in reports list !903 @peter.toennies
- Orga can send bananas again !937 @NerdyProjects
- Mails from trash can be deleted again @peter.toennies
- Redirect to the forum instead of showing 404 on deleting the last post of a thread. #626 !953 @ctwx_ok
- Moved "forum-title-buttons" into the second line #591 !949 @chriswalg
- Fixed order of event invites on the dashboard !938 #608 @peter.toennies
- Walls do now show 60 comments instead of 30 !940 #630 @peter.toennies
- It is now possible for every foodsaver to see and join a pre existing event links of a district or working group. This foodsaver only needs to be part of this specific group where the event was created #273 !912 @jofranz
- Fixed scroller maxHeight for fair-share-points and AMB foodsaver list !972 @jofranz
- Show Warning and prevent save if sleeping timespan has no complete date given #632 !957 @fs_k
- Fixed and moved ipIsBlocked method which is used on the team page contact form. Added minutes to warning message !974 @jofranz
- Enable ambassador's new threads by default. #614 !967 @ctwx_ok
- Fixed fancybox loading (apple) and navigation sprites !977 #644 !1433 #717 @jofranz @ChrisOelmueller
- Updates from the regional "bot-forum" / ambassador board are now shown on dashboard #40 !994 @jofranz
- Fixed hidden attribution-line on main map !980 #661 @mr-kenhoff
- Fixed date display for chats in the top bar overlay. !988 @ctwx_ok
- Passport generation is now reliable working with all genders. !997 #665 @mr-kenhoff
- Don't return outdated baskets via the REST API !1008 @dthulke
- Fixed saving an edited quiz answer !1006 #408 @svenpascal
- Added contact form email information to email body/text as a workaround to make it possible for people to reply !979 @jofranz
- Return images attached to a wall post in the WallRestController !1013 @dthulke
- Don't show forum updates from deleted users on dashboard !1011 #666 @alex.simm
- Fixed role description for gender 'diverse' !1016 #674 @svenpascal
- Fixed broken quiz after refactoring !1017 @svenpascal
- Verify quiz session status without having a second learning break !1018 #673 @svenpascal
- Show message and redirect page after deleting an account !1028 #533 @alex.simm
- Fixed the createThread call inside the ForumRestController !1031 @ctwx_ok
- Remove forum thread subscriptions when leaving group !1020 #593 @alex.simm
- Fixed sorting of dashboard entries on initial loading !1035 #681 @ctwx_ok
- When logging in, referenced redirects work now. !1034 #563 @peter.toennies
- Open link to markdown description in a new window !1050 #698 @chriswalg
- Open wiki.foodsharing.de in top menu bar in new window !1051 @chriswalg
- Deleting report notes now possible for Orga and admins of the report team. Writing user notes now possible for orga only !1038 #537 @peter.toennies
- Fix appearance of event accept/decline buttons on small screens !1027 #640 @petersielie
- Do not allow signing out of past pickups !1058 #633 @alex.simm
- The avatar sleeping mode in forum is visible now. !1055 #679 @chriswalg
- Fixed occupied one-time pickups that showed up unoccupied !1059 #633 @alex.simm
- Fixed end date not being displayed when editing existing multi-day events !995 #277 @tihar
- Link in chat-message notification email now leads to corresponding conversation !1064 #703 @rastadapasta
- Improve the readability of the data protection agreement during registration #652 !1056 @chriswalg
- Only show food baskets which are not timed out on dashboards basket range and latest list !1004 @jofranz @peter.toennies
- Fixed invisible overbooked pickups !1069 #633 @alex.simm
- Workgroups overview optimized for mobile view #702 !1063 @chriswalg
- Bugfix for sentry issue regarding the #vue-pickuplist !1074 @ctwx_ok
- Removed question form for data privacy !1077 #166 @chriswalg
- Fixed the check for empty address data on the foodsaver dashboard !1076 @peter.toennies
- Bugfix for empty pickup list !1078 @ctwx_ok
- Set width 50px for user pics in region member list !1080 @chriswalg
- Put the footer on pages with less content at the bottom of the page !1087 #590 @chriswalg
- Bugfix for end date being required when creating single-day event !1084 @tihar
- Removed the obsolete and insecure foodsaver bubble for our map !1093 @peter.toennies
- Removed forum subscriptions for people who left a district or workgroup !1071 #655 @alex.simm

## Refactoring
- Refactored profile from WorkGroupModel to WorkGroupGateway !898 #9 @svenpascal
- The page does not use fullpage anymore. New landing page !597 #393 @theolampert
- Reduce Load on every Request to RegionControl !921 @alangeker
- reduced codebase around map markers. !588 @chriswalg @peter.toennies
- Refactored conversations API and related javascript !592 @theolampert
- Converted nightly maintenance methods deactivateOldBaskets() and deleteUnconfirmedFetchDates() into gateway !976 @jofranz
- Refactored team page. Got rid of legacy methods !974 @jofranz
- Refactored fetch weight menu handling and moved weight methods into a helper class !1002 @jofranz
- Refactored QuizModel into a QuizGateway !998 #9 @svenpascal
- Refactored pickup slot deletion methods, kicked out duplicated code/vars and deleted not used code !968 @jofranz
- Use new storePermissions instead of chaining previous permission checks in stores !990 @jofranz
- Refactored the WallPost module !1038 @peter.toennies

## Dev/Test/CI stuff
- enable functional tests (symfony kernel running inside conception; for limits see inside tests/functional folder) !884 @NerdyProjects
- Use BSD tools in scripts/clean instead of GNU tools for Unix (macOS/OSX) bash. !889 @svenpascal
- updated codeception to version 3 @peter.toennies
- include rules from !511 in devdocs @flukx
- updated eslint to v6, eslint-config-standard to v14, eslint-plugin-node to v10, and eslint-plugin-html to v6 @peter.toennies
- updated webpack loaders. sass to v8, eslint to v3, style to v1, css to v3, file to v4, null to v3, url to v2, and mini-css-extract-plugin to v0.8 @peter.toennies
- update watch to version 1 @peter.toennies
- add caching for volumes in dev mode !1075

# 2019-08-30 Hotfix
- Handle chat messages according to their stored encoding be ready for !887 @NerdyProjects

# 2019-06-17 Hotfix
- Have unique single additional pickups to comply with current master backend !934 @NerdyProjects

# 2019-06-09 Hotfix
- InfluxDB Metrics via UDP !882 @alangecker
- Allow receiving emails with an empty body for the internal mailing system @NerdyProjects
- Updated deployment for new production server @alangecker @NerdyProjects
- remove verbose output of bounce mail processing and mail fetcher, add bounce mail stats to influx db @NerdyProjects
- remove progressbar from cron scripts !919 @NerdyProjects

# 2019-05-17 Hotfix

- Clarify message when you cannot sign up for a pickup.
@NerdyProjects has been working on the pickup backend in the last months so the website does not allow you to sign up for pickups that are further in the future than the setting in the store allows (1-4 weeks).
The frontend does not yet follow that behaviour (showing pickup slots always for more days than allowed to sign up), but if you want to sign up, the backend disallows that and you get an error message.
@NerdyProjects currently works on redoing the pickup frontend as well and we hopefully get it shiny in a few days :-)
- fixed the switched store publicity settings @peter.toennies

# 2019-05-17
Hey again,
another release for you. Nothing big, but a lot of small. Most noticeable things will be changed email templates as well as more buttons which properly work on mobile now.

## Major changes

## Features
- gender and pickup statistic information in regions #582 !858 @fs_k
- Messages to working groups (AG) are now sent in email copy to the member sending them. #493 !774 @zommuter
- API to display report per region allowing ambassadors to work on their reports !529 #296 @NerdyProjects
- Vue.JS implementation of reports page !529 #296 @theolampert
- It is now possible to sign out from my main region (and chose a new one) #26 !778 @peter.toennies
- Made email notifications great again #450 @zommuter:
    - Responsible user in the FROM field !798
    - Message excerpts in the SUBJECT !800, !838
    - Briefer messages for better content preview !805, !806
- Reworking menue (Added "Aktionen" menu item, made some pages available also in logged-in menu, added several new pages on politics and transparency) #473 !739 @fs_k @D0nPiano
- Fit popup dialogs to smartphone and desktop screens with different conditions !826 @jofranz :
    - Profile: verfication and pass history (BOT functionality)
    - Profile: report user
    - Store: manually add team members (BIEB functionality)
    - Store: change automatic pickup times (BIEB functionality)
    - Store: slot join
    - Store: slot leave
- Added button/badge to user profile with amount of food baskets created. Enabled postCount as a button/badge even if the person has 0 posts #466 !788 @jofranz
- Chat section "All messages" is now accessible on mobile !670 #419 @Defka @jofranz @D0nPiano
- Showing number of foodsharers in statistics. Small graphical changes. !832 @jofranz @peter.toennies
- Ambassadors and orga-members need to be approved by store managers for pickups. !415 #225 @fs_k
- Ambassadors of Austria are automatically included to the Austrian AMB working group @peter.toennies
- Added logout Rest endpoint !866 @alex.simm

## Bugfixes
- Orga can delete quizzes #364 !767 @fs_k
- Return 404 instead of 403 for non-existing forum threads !761 @NerdyProjects
- Store member status icons suitable to status on ambassador view of profiles !766 @flukx
- Properly escape store names in request popups !778 @NerdyProjects
- Clarify that PLZ/Ort have to be selected in the map and cannot be modified manually #497 !790 @zommuter
- Non-followers can comment on Fairteilers again #457 !691 @janopae
- Add CSP headers that work with Austria/Switzerland sites !793 @nicksellen
- Allow blog posts to be properly formatted !795 @djahnie
- Some email templates still referred to lebensmittelretten.de instead of foodsharing.de !805 @zommuter
- Fixed bug in Database.php class where count() is returning bool (0/1) instead of the actual amount in int !788 !813 @jofranz
- Fix excerpt generation (dashboard overview, email excerpts, ...) to be unicode aware and not return more characters as it should !812 @NerdyProjects
- Put more useful information in forum moderation emails and workgroup contact emails !812 @NerdyProjects
- Fix width of inputfields to a defined value !834 @peter.reutlingen
- Mailbox users can be autocompleted/managed again !852 @NerdyProjects
- When a orga views a profile of a user who has never logged in before, the last login date shown now "never" instead of todays date !846 @Caluera
- Also display sleeping foodsavers in members list !861 @jofranz
- Fix in the AddBasketAction to allow setting a description and message preferences while creating a basket in the Android App !863 @dthulke
- Workaround to fix selecting adresses in Vienna !854 @dthulke
- Make comments visible again on fair-share-points for non-registered users !867 @fs_k @jofranz
- Show pickup amount in store if set over 50 kg. #546 !862 @svenpascal
- Tidy up content security policy !870 @NerdyProjects

## Refactoring
- removed the geoClean and LostRegion modules !756 #103 @peter.toennies
- refactored profile from model to gateway !782 #9 @peter.toennies
- API does not expose full URL to avatar images to allow the frontend to chose the resolution !529 @NerdyProjects
- FluentPDO Query builder integrated to try it out !529 @NerdyProjects
- Refactored mailbox from model to gateway !803 #9 @peter.toennies
- Removed the geoClean and LostRegion modules !756 #103 @peter.toennies
- Refactored profile from model to gateway !782 #9 @peter.toennies
- Forbid to signup for non-existant pickups !783 @NerdyProjects
- Handle pickup signups via rest api !783 @NerdyProjects
- Removed the library class Func.php !716 !750 !776 !784 !797 @peter.toennies
- Get rid of any infomail setting related redis "caching" as all information was already available fresh from the database !812 @NerdyProjects
- Refactored login from model to gateway !828 #9 @peter.toennies
- Completely replaced flourish fDate with Carbon time in niceDate() and ProfileView details for AMBs !835 @jofranz
- Renamed some variables in StoreUserControl.php from German to English. !862 @svenpascal
- Extracted method mentionPublicly($id) in StoreUserControl.php to improve functions’ level of abstraction. !862 @svenpascal


## Dev/Test/CI stuff
- Adjust devdocs to being open source !823 @flukx
- Mention test artifacts under „Troubleshooting in devdocs“ !845 @flukx
- add section about font awesome in devdocs !842 @flukx
- Several reference texts in devdocs about used technologies !741 @flukx
- Use CI built assets and vendor for deployment !768 @NerdyProjects
- Use php-cs-fixer, parallel-lint and phpstan in CI build:lint step !775 @NerdyProjects
- Update mocha to version 6 @peter.toennies
- Run all jobs except test and deployment on shared CI runners !780 @NerdyProjects
- Run frontend lint/test/build and backend lint/build in one CI job each !780 @NerdyProjects
- Add php-cs-fixer to `./scripts/lint-php`, remove `./scripts/fix-codestyle` in favour of `./scripts/fix` !781 @NerdyProjects
- Remove `./scripts/build-assets` as they are continuosly built by webpack-dev-server !781 @NerdyProjects
- Make sure old CI containers are removed in test stage !787 @NerdyProjects
- added /nbProject to .gitinore !791 @fs_k
- Seed data for reports !529 @NerdyProjects
- Email templates are no longer stored in the database but the repository #502 !805 !839 @zommuter
- Phase out EmailTemplateAdmin !805 @zommuter
- Flush redis before running tests #135 !807 @nicksellen
- Test email templates for new forum messages !812 @NerdyProjects
- Update copy webpack plugin to version 5, jsdom to v 15, and dotenv to v 8, eslint-plugin-node to v 9, vue-eslint-parser to v 6, and null-loader to v 1 @peter.toennies
- Wrote acceptance tests for showing fetched quantity and store public mentioning (StoreUserCest.php). !862 @svenpascal

# 2019-02-25 Hotfix

We have to do some database maintenance for !792 which hopefully works fine and fast...

## Bugfixes
- Fix truncation of messages when using emojis by using utf8mb4 charset #338 !792 @nicksellen
- Fix forum "Antworten" button !786 @nicksellen
- getBezirk in region admin tool fails for all regions that have stores in them #495 !777 @NerdyProjects

# 2019-02-21

We are happy to announce another release which got hundreds of hours of love, lastly from more then 10 people participating in the 2019 february foodsharing.de hackweek, sitting together since last friday at Kanthaus near Leipzig.

This release is a milestone as we finally managed to tackle some issues that increase the security of foodsharing.de and by that the privacy of all our users.

We are very proud to finally release foodsharing with an **AGPLv3** licence, making it finally a [Free and open-source software](https://en.wikipedia.org/wiki/Free_and_open-source_software).

## Major changes
- A security focussed code audit has been done by @alangecker which lead to fixing more than 50 related issues, from which 10 were of critical and 6 of high severity #472
- AGPLv3 licence added. The [Gitlab repository](https://gitlab.com/foodsharing-dev/foodsharing) is now publically visible
- [CSRF](https://en.wikipedia.org/wiki/Cross-site_request_forgery) protection for most requests to avoid malicious requests deleting accounts or changing data without the users intention to do so
- Lots of [XSS](https://en.wikipedia.org/wiki/Cross-site_scripting) vectors have been closed by setting the correct content type on json responses
- Removed backend code to stop old android app *foodsharing lebensmittelretten* (*de.lebensmittelretten.app*) from working. The development team cannot take the responsibility for using this app as it implements very bad practices regarding security. We advice all current and recent users of that app to change the password they used on foodsharing.de.
- We are happy with the continuous process of cleaning up our code and reimplementing more and more parts as proper API requests and getting rid of spaghetti-javascript

## Features
- On dashboard there now is a symbol indicating the confirmation status of a pickup !661 @jofranz
- Pre-fill end date of pickup history with today's date for comfort reasons !660 @jofranz
- Added the amount of events conditionally to the dashboard event headline in case there is more than one event !650 @jofranz
- Added a new button to the contextmenu which appears by clicking the profilepic in shops #302 !671 @peter.reutlingen
- Make date in event a mandatory field #436 !669 @tihar
- Added API endpoints for basket pictures !671 @alex.simm
- Allow use of markdown in Fair-Teiler description !690 @NerdyProjects
- Joining regions REST API !696 @NerdyProjects
- Added member list for districts and work groups !697 @djahnie
- Prevent group admins to be able to access the passport generation page !706 #392 @jofranz
- Start page content over content manager #470 !701 @fs_k
- Added profile status infos for store and pickup entries for ambassadors !705 @jofranz
- Scale down font size on passports for long names !685 @NerdyProjects
- CSRF protection for API requests !715 @alangecker
- Disabled caching searchindex for uptodate results !727 @NerdyProjects

## Bugfixes
- Search index is now shared between deployments so we avoid a lot of javascript errors regarding failed requests !657 @NerdyProjects
- Fixed bug in #302 goto_profile_from_teamsite !671 with !675 @peter.reutlingen
- Fixed an SQL injection in an FoodsaverGateway method @alangecker
- Properly escape Fair-Teiler names in all occurrences !690 @NerdyProjects
- Avoid strip_tags on bell data !691 @NerdyProjects
- Permission checks when joining regions !696 @NerdyProjects
- Fixed the bug that the number of pickups in the team list isn't shown when the name is too long. #381 !688 @peter.reutlingen
- Only foodsavers add themselves to working groups !713 @NerdyProjects
- Only allow edting regions as an orga user !714 @NerdyProjects
- higher entropy for security & privacy related tokens !709 @alangecker
- Fix recently broken quiz session storage !730 @NerdyProjects
- Fix broken permission checks in foodsaver xhr module !731 @NerdyProjects
- Fix broken permission checks in geoclean xhr module !731 @NerdyProjects
- Fix broken permission checks in mailbox xhr module !731 @NerdyProjects
- Fix path traversals vulnerabilities !723 @alangecker
- Fix multiple XSS vulnerabilities !722 @alangecker
- Properly show quiz as succeeded when errorpoints match max. allowed errorpoints @NerdyProjects
- Fix wrong stated relationship between user role and home district on user dashboard. Add information about user pickups to dashboard.!748 @pmayd
- Only allow creation of stores in a region you are member of @NerdyProjects

## Refactoring
- replaced many outdated jquery functions !655 @peter.toennies
- remove unused methods in XhrMethods !694 @NerdyProjects
- trigger Fair-Teiler wallpost notifications in backend !700 @NerdyProjects
- removed the old qr code library and chaged the current qr on the fs-passes to show the fs profile !685 #144 @peter.toennies
- trigger fairteiler wallpost notifications in backend !700 @NerdyProjects
- use API endpoint to delete users to avoid CSRF problems !717 @NerdyProjects
- use API endpoint to delete regions/workgroups to avoid CSRF problems !719 @NerdyProjects
- Refactored loop for avatar placement in event view. Added amount as a parameter !718 @jofranz
- removed unused php, js and css code !720 @alangecker
- user normalisation in conversations API endpoint @alex.simm
- remove unused quickprofile method !755 @NerdyProjects
- fix a few linter warnings !755 @NerdyProjects

## Dev/Test/CI stuff
- better webpack splitting !681 @nicksellen
- disable backup_globals for PHPUnit to have unit tests working in dev again !696 @NerdyProjects
- fix xdebug by enabling x-forwarded-for header in webpack devserver !725 @NerdyProjects
- PHP always runs as www-data inside docker to work around permission problems @NerdyProjects

# 2019-02-19 Hotfix
- Backport some changes that were needed for yesterdays hotfix
- Conversation API returns name (or null) !658 @nicksellen
- Fixup conversation header display !658 @nicksellen

# 2019-02-18 Hotfix
- fix for 9 SQL injection vulnerabilities #472 @alangecker
- Fix mass mail sender and email output formatting !707 @NerdyProjects

# 2019-02-16 Hotfix
- Fixed an SQL injection in a FoodsaverGateway method @alangecker

# 2019-02-02 Hotfix
- readded adresspicker functionality to Fair-Teiler creation page !668 @peter.toennies
- Devdocs: Restructured and added content, fixed typos and punctuation, and unified spelling !617 @llzmb

# 2019-01-25
Matthias: "Are there any concerns about merging the addresspicker / map / geolocation to production?"
Peter: "I'd even prefer to go completely from beta to prod. The current state looks fine for me. Less work for you, more features for us... "

Here we go :-) Just a month after our last release. Expect the next one in a month, at the end of the next hackweek happening at Kanthaus.

## Major changes
- All maps use free tiles from wikimedia now
- Geolocation (Converting address into geographical coordinates) now using a public service provided by komoot instead of google
- JQuery 3
- PHP 7.3

## Features
- Updated tinysort to v3 @peter.toennies
- Added link from names in chatbox title to profiles #100 !614 @colomar
- You can now call a BIEB via the lower info box of a store with just one click !609 @jofranz
- Changelog page now supports links to issues with a # symbol followed by a number like it has been for merge requests before !622 @jofranz
- update htmlpurifier to version 2 !594 @peter.toennies
- add security headers (beta only) !633 @nicksellen
- changed tile maps to wikimedia !639 @alex.simm
- Use typeahead-address-photon for address autocomplete. Update leaflet and typeahead for recent NPM versions in the same go !640 @NerdyProjects
- link top area of welcome message to profile #427 !635 @Defka
- Added a number conditionally to the dashboard event view if there is more than one event !650 @jofranz

## Bugfixes
- Improve/correct user profile badge count !612 @pmayd
- Datepicker for fetch slots in stores increased to fit 6 week rows #417 !621 @peter.reutlingen
- Changed color of the breadcrumb sitenavigation below the topbar !636 @peter.reutlingen
- Remove fetchrate for users with zero pickups !646 @jofranz

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
- Use PHP 7.3.1 in Dev/Test/CI !644 @NerdyProjects
- Some restrictions for use of composer !627 @peter.toennies

# 2019-01-24 Hotfix

- Put information about email address change on front page @NerdyProjects

# 2019-01-22 Hotfix

- Do not send emails to bouncing addresses !645 @NerdyProjects
- Do not ask users why they want to delete their account !647 @NerdyProjects
- Support falsy (0, '') values for variables in client side translations !641 @NerdyProjects

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
- shows hint in food basket about public information #373 !570 @fs_k
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
- stat_fetchrate is calculated correctly and shown in profile added tvalue in select !598 #281 @fs_k
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
- Only accepted store members see updates on dashboard !412 @fs_k
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
- Do not rely on $\_SERVER\['HTTP\_HOST'\] being set #263 !510 @NerdyProjects
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
- updated corejs to v 3 !1043 @peter.toennies

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
- Show Report ID in Detail Report window #246 @fs_k
- updated wording in respect to new report handling procedure !454 @peter.toennies

## Bugfixes
- removed XSS-possibility in xhr_out method. !370 @theolampert
- Fix pickup slots !390 #215 @nicksellen
- fixed wrong gendering of AMBs in region view and profile view. !386 #214 @peter.toennies
- Added a format placeholder to date input #217 @theolampert
- reduced the height of store info popups by removing the warning frame. !388 #216 @peter.toennies
- fixed wrong usage of gateway in API. !400 @peter.toennies
- fixed missalignment in future-pickups list. !389 #136 @EmiliaPaz
- Regaining support for mobile Safari 10 !396 #221 @michi-zuri
- fix relative loading of some xhr/other urls !422 @nicksellen
- fixes user autocomplete fetching for conversation creation
- fix profile sleeping hat variable #243
- fix bug in region dynatree loading #244 !444 @nicksellen
- Only show forum post removal button when the user is allowed to delete a post !456 @NerdyProjects

## Refactoring
- Extract StoreUser module javascript !358 @nicksellen
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
- show date and comment of sleeping hat on profile page. !427 #178 by @fs_k
- show home district on profile page. !427 #237 by @fs_k
- sort fairtiler list by name. !357 #171 by @fs_k
- Store Managers business card creation for region. Remove country card. !76 by @fs_k
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
- data privacy : removed foodsaver / ambassador selection from map. #165 by @fs_k
- fixed potential security issue in profile picture uploads. !371 #84 @theolampert
- updated fpdi plugin to v2.0.2 !351 #168 by @peter.toennies

## Refactoring
- complete tidying up of all team related files !321 by @peter.toennies
- replaced the PREFIX keyword in the whole source !339 #421 by @peter.toennies
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
- remove ability for ambassador to add any foodsaver to his/her district !328 #405 by @fs_k and @peter.toennies

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
- Page with list of communities for Austria/Germany/Switzerland. !286 by @fs_k
- Single appointment can be set to "appointment cancelled" (=0 Slots) !372 by @fs_k
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
- phone number validatino removed from login. Mobile Phone instead of landline phone asked. !361 by @fs_k

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
- Removed not working store creation button and map view selector from the list of stores. !188 @peter.toennies

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
* @fs_k
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
- Reports list can be sorted by main region of the FS !151 @fs_k @peter.toennies

## Bugfixes

- Fix multiple warnings/notices regarding accessing undefined variables !192 @NerdyProjects
- Fix spinning apple when in profile/conversation with a user without a profile picture !172 @NerdyProjects
- Move login button in navigation a bit to the right to be always clickable !162 @NerdyProjects
- Set the pages timezone globally to Europe/Berlin to not having to rely on server settings !256 @NerdyProjects
- Foodsharers and unverified Foodsavers are no longer able to create business cards !145 @fs_k @peter.toennies
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

* see [previous changelog](https://wiki.foodsharing.de/Foodsharing.de_Plattform:_%C3%84nderungshistorie)ˆ
