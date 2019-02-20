# Contributing

If you have any questions please reach out to us via slack: [yunity slack](https://slackin.yunity.org/) and join the #foodsharing-dev channel.

## Coding guidelines

We do have a few coding guidelines, mentioned at various places in these devdocs.
In general, please use your common sense and make it fit with the existing code.

## Submitting an issue

If you found an issue on the foodsharing website, then please submit it to our GitLab [issues](https://gitlab.com/foodsharing-dev/foodsharing/issues).

If you feel comfortable submitting a fix (or if you like to try ;) ) too, then follow the next section.

## Submitting a change

You can either submit your own issue and work on it or work on existing issues. Issues that are suitable for newcomers are labeled as [starter tasks](https://gitlab.com/foodsharing-dev/foodsharing/issues?label_name%5B%5D=starter+task).

To work on an issue:

1. Check if there is an issue for the change in the GitLab [issues](https://gitlab.com/foodsharing-dev/foodsharing/issues).
  * This is a seperate project as it is public and the repo is not.
  * If you are just submitting a very small change or a doc fix, then don't worry about creating an issue.
2. Create a new git branch, prefixed with the issue number rather than fork the repo, as it makes permissions trickier.
  * For example, the issue number `56` would have a branch named `56-some-descriptive-words`.
  * Optionally, add your name to the branch name; for example, `56-nicksellen-some-descriptive-words`.
3. Make your changes.

To submit your change:

1. Check if the code style is fixed before commiting, by running `./scripts/fix-codestyle`.
2. Check if the tests pass locally, by running `./scripts/test`.
3. Create a merge request to master for your branch early on.
  1. Select the template "Default".
  2. Prefix the name of the merge request with `WIP:`.
4. Make sure your merge request checks all the checkboxes in the "Default" template (and check them in the description).
5. Once you think your branch is ready to be merged, remove the `WIP:` prefix from the name of your merge request. Rebase your branch onto master (which might have developed since your branching). It is OK to force-push (`git push origin <your_branch_name> -f`) after rebasing.
6. Submit your merge request.

The next steps will be:

* An approver will get back to you with feedback or change requests. Please have some patience if this does not happen right away.
* Once the approver considers your changeset ready to be made, they will merge it into the master branch.
* The master branch will be deployed automatically to [beta.foodsharing.de](https://beta.foodsharing.de), where you can try it out (uses production database).
  * See [environments on GitLab](https://gitlab.com/foodsharing-dev/foodsharing/environments) for an overview of the different environments.
* Hang around and see if people in #foodsharing-beta on [Slack](https://yunity.slack.com/) find any issues, etc.
* At some point in the future, once a few changes have been collected, they will all be deployed to production.

## Troubleshooting

(Work in progress.)

## Testing

You can run the tests with `./scripts/test`,
for your second and following runs, you can use `./scripts/test-rerun` which runs much quicker
(as long as we keep writing the tests to run idempotently, please do!).

So far, end to end tests (called _acceptance tests_ in codeception) work nicely.
They run with a headless Firefox and Selenium inside the Docker setup and they are run on CI build too.

We are restructuring the code to enable unit testing.  
Related issue: [Incremental refactor](https://gitlab.com/foodsharing-dev/foodsharing/issues/68).

The state created during testing is not thrown away, and you can visit the test app
[in your browser](http://localhost:28080/), and it has
[its own phpmyadmin](http://localhost:28081/).

If you want to run the tests with debug mode turned on, use: `./scripts/test --debug`.

If you just want to run one test, pass the path to that test as an argument,
e.g.: `./scripts/test tests/acceptance/LoginCept.php`.
