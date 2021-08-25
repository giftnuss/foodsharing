# Making and submitting changes <!-- omit in toc -->

Deutsche lange Version dieser Datei: [Einführung in Git und unser Arbeiten](/contributing_DE.html)

What you'll find here:

- [General](#general)
- [Submitting an issue](#submitting-an-issue)
- [Submitting a change](#submitting-a-change)
  - [Becoming a member](#becoming-a-member)
  - [Working on an issue](#working-on-an-issue)
- [Testing](#testing)

## General

If you have any questions please reach out to us via slack: <https://slackin.yunity.org/> and join the `#foodsharing-dev` channel.

## Submitting an issue

If you found an issue on the foodsharing website, then please submit it to our GitLab issues: <https://gitlab.com/foodsharing-dev/foodsharing/issues>

If you feel comfortable submitting a fix or if you would like to try and learn by doing and other team members in the process, follow the next section.

## Submitting a change

### Becoming a member

As a “member” on our versioning system GitLab you can

- create and push to branches within the repository (except master).

- see confidential issues.

- set labels to issues.

- assign yourself to issues (to tell others that they do not need to start on them).

After creating a GitLab account and applying for membership, write a few introducing lines about yourself on the Slack channel <https://slackin.yunity.org/> `#foodsharing-dev`.
You can apply for membership by clicking the *Request Access* button in the GitLab UI <https://gitlab.com/foodsharing-dev/foodsharing> after you created your account.

### Working on an issue

You can either submit your own issue and work on it or work on existing issues.
Issues that are suitable for newcomers are labeled as starter tasks: <https://gitlab.com/foodsharing-dev/foodsharing/issues?label_name%5B%5D=starter+task>

One-Time-Setup:

1. Create a GitLab account <https://gitlab.com/users/sign_up> or use an existing account.

2. Create a SSH key, e.g. via terminal command `ssh-keygen -t ed25519 -C "my@email.com"`.
   Then, as a name, enter `foodsharing_ssh_key`, for example.
   A passphrase is optional.

3. Upload the generated key to your GitLab user profile keys <https://gitlab.com/-/profile/keys> as a new entry to authenticate your computer to the foodsharing GitLab via SSH.

4. Install Git: <https://git-scm.com/book/en/v2/Getting-Started-Installing-Git>

5. Clone the git repository by executing: `git clone git@gitlab.com:foodsharing-dev/foodsharing.git`

To make a desired change, please work on your own branch named after the issue number (skip the following paragraph, if you already know how to use Git & collaborate in GitLab):

1. Check if an existing issue has already been created for the change in the public GitLab issue backlog: <https://gitlab.com/foodsharing-dev/foodsharing/issues>
   - Hint: If you are just submitting a very small change or a doc fix, then don't worry about creating an issue.
    If you find an issue, only work on it if there is no assignee yet in the issue details bar at the right.
    If there is an assignee, someone else is already working on the ticket.
    For a bigger change, discuss if you can help out or split the work into sub-tasks.

   - Click the "assign yourself" button in the right bar of the issue to indicate that you started working on the issue.

2. Update to the newest changes on master branch by executing `git checkout master` and `git pull`.

3. Create a new local git branch for your local changes, prefixed with the issue number, by executing the command `git checkout -b <issue-id><your-name (optional)><some-descriptive-words>`.
   - For example, the issue number `56` could have a branch named `56-nick-sellen-some-descriptive-words`.

   - Hint: Best practice is to work in your own branch and never in the master branch.
     You can create more than one branch at once to work on different issues simultaneously.

4. Make your changes and push them via the following commands.

   4.1. `git status` to see which files changed.

   4.2. `git add <desired files>` (`git add .` will add all changed files to version control.),

   4.3. `git commit -m "<description of change>"` and `git push -u origin HEAD` initially to set the upstream name equal to the branch name you created locally.

   4.4. If your changes are very small or only about documentation, you can consider using the push option `git push -o ci.skip` which disables running the build and test on the Gitlab server.

To submit your change:

1. Check if the code style is fixed before commiting, by running `./scripts/fix-codestyle-local` (or if that does not work by running the slower `./scripts/fix`).

2. Check if the tests pass locally, by running `./scripts/test`.

3. Create a merge request to master for your branch early on.

   3.1. Select the template "Default".

   3.2. Prefix the name of the merge request with `Draft:`.

4. Make sure your merge request checks all the checkboxes in the "Default" template (and check them in the description).

5. Once you think your branch is ready to be merged, remove the `Draft:` prefix from the name of your merge request.
   Rebase your branch onto master (which might have developed since your branching).
   It is OK to force-push (`git push origin <your_branch_name> -f`) after rebasing.

6. Submit your merge request.

The next steps will be:

- An approver will get back to you with feedback or change requests.
  Please have some patience if this does not happen right away.

- Once the approver considers your changeset ready to be made, they will merge it into the master branch.

- The newest version of the master branch will be deployed automatically to <https://beta.foodsharing.de/> after some time, where you can try it out (uses production database).
  - See <https://gitlab.com/foodsharing-dev/foodsharing/environments> for an overview of the different environments.

- After your MR has been merged, you are responsible to create a testing issue in the Beta Testing forum: <https://foodsharing.de/?page=bezirk&bid=734&sub=forum>:
  - Consider writing a detailed description **in German**.
  - Describe in a few sentences, what should be tested from a **user perspective**.
  - Also mention different settings (e.g. **different browsers**, roles, ...) how this change can be tested.
  - Be aware, that also **non technical** people should understand.

- Hang around and see if people in `#foodsharing-dev` on Slack at <https://yunity.slack.com/> find any issues, etc.

- At some point in the future, once a few changes have been collected, they will all be deployed to production.

## Testing

It is recommended to only run individual tests locally.
To do so, pass the path to that test as an argument to the test script,
e.g.: `./scripts/test tests/acceptance/LoginCept.php` to run only the tests within this test bundle.

You can run a single test by `./scripts/test <path>:<method_name> <parameters>`, e.g. `./scripts/test tests/api/StoreApiCest.php:canWriteStoreWallpostAndGetAllPosts --debug`.

If you want to run the tests with debug mode turned on, use: `./scripts/test --debug`.

You can run all the tests at once with `./scripts/test` locally (a lot of time and good hardware required).
For your second and following runs, you can use `./scripts/test-rerun` which runs much quicker.
(as long as we keep writing the tests to run idempotently, please do!).

So far, end-to-end tests (called _acceptance tests_ in codeception) work nicely.
They run with a headless Firefox and Selenium inside the Docker setup and they are run on CI build too.

We are restructuring the code to enable unit testing.
Related issue: <https://gitlab.com/foodsharing-dev/foodsharing/issues/68>

The state created during testing is not thrown away, and you can visit the test app
in your browser :<http://localhost:28080/>
and it has its own phpmyadmin: <http://localhost:28081>
