# UNRELEASED [release date]

## Releases notes

## Breaking changes

## Features
- Test environment allows testing of file downloads as well !165 @NerdyProjects
- Show current commit in footer as well as use it in sentry if errors occur !153 @NerdyProjects
- Reports list can be sorted by main region of the FS. !151 by @k.miklobusec and @peter.toennies 

## Bugfixes
- Output from failed tests is now collected with the test job. !165 @NerdyProjects
- Move login button in navigation a bit to the right to be always clickable. !162 by @NerdyProjects
- Set the pages timezone globally to Europe/Berlin to not having to rely on server settings. !256 by @NerdyProjects
- Foodsharers and unverified Foodsavers are no longer able to create business cards. !145 by @k.miklobusec and @peter.toennies
- Breadcrumb links in forum view are working now. !142 by @raphaelw and @NerdyProjects
- Lots of corrections in spelling and grammar. !140, !118 by @peter.toennies
- Consistancy in naming: All uses of "Region" are now called "Bezirk". !141 by @peter.toennies
- Alphabetical order in the orga-menu. !160 by @peter.toennies

## Refactoring
- Enable composer autoloader and initial src folder. !157 by @tiltec

## Docker environment
- Run mkdirs using exec so it works in CI !164 @NerdyProjects

# v1.0.0 [date?]

* everything so far
