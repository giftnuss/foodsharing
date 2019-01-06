# Contributing

If you have any questions please pester us in [yunity slack](https://slackin.yunity.org/) in #foodsharing-dev channel.

## Coding guidelines

We do not have specific coding guidelines yet, please use your common sense and make it fit with the existing code.

## Found an issue?

Submit an issue in our [issues](https://gitlab.com/foodsharing-dev/foodsharing/issues)!

If you feel comfortable submitting a fix too, follow the next section.

## Submitting a change

* Ensure there is an issue for the change in the [issues](https://gitlab.com/foodsharing-dev/foodsharing/issues)
  * this is a seperate project as it is public, and the repo is not
  * If you are just submitting a very small change or a doc fix don't worry about creating an issue
* Make your changes in a new git branch, prefixed with the issue number rather than fork the repo, as it makes permissions trickier)
  * e.g. issue number `56` would have a branch named `56-some-descriptive-words`
  * optionally add your name, e.g. `56-nicksellen-some-descriptive-words`
* Ensure the tests pass locally `./scripts/test` 
* Create a merge request to master for your branch early on
  * select the template "Default"
  * prefix the name of the merge request with `WIP:`
* Make sure your merge request checks all the checkboxes in the Default template (and check them in the description!)
* Once you think your branch is ready to be merged, remove the WIP prefix from your merge request
* An approver will get back to you with feedback or change requests (have some patience if this does not happen right away)
* Once the approver considers your changeset ready to be made, they will merge it into the master branch
* The master branch will be deployed automatically to beta.foodsharing.de where you can try it out (uses production database)
    see https://gitlab.com/foodsharing-dev/foodsharing/environments for an overview of the different envuironments
* Hang around and see if people in #foodsharing-beta on Slack ( yunity.slack.com ) find any issues, etc...
* At some point in the future once a few changes have been collected it'll be deployed to production

## Testing

You can run the tests with `./scripts/test`,
once you have run them once you can use `./scripts/test-rerun` which runs much quicker
(so long as we keep writing the tests to run idempotently, please do!).

So far end to end testing is working nicely (called acceptance tests in codeception).
They run with a headless firefox and selenium inside the docker setup, they are run on CI build too.

We are working on [restructing the code](https://gitlab.com/foodsharing-dev/foodsharing/issues/68)
to enable unit testing.

The test contains stay around after running, and you can visit the test app
[in your browser](http://localhost:28080/), and it has
[it's own phpmyadmin](http://localhost:28081/).

If you want to run with debug mode turned on use: `./scripts/test --debug`.

If you just want to run one test pass the path to that test as an argument,
e.g. `./scripts/test tests/acceptance/LoginCept.php`
