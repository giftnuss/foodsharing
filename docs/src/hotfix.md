# Hotfix deployment

## Preparation and merge:
* Start on production branch and create new branch from it (e.g. branch `hotfix-20200615`)
* Cherry pick commits from master branch (`git cherry-pick {commitId}`)
* picking commits from MRs / branches that have not been merged into master creates differences between master and production that will be annoying in the next release
* optionally check git log history and diff (e.g. `git diff origin/production`)
* add new hotfix section to changelog
* there might be other merge conflicts in the changelog -> fix them manually (maybe change auto merge for the changelog in the future?) and commit
* the hotfix branch can be pushed to run the pipeline and to check if the tests are ok

#### to merge:
* either switch back to production branch and merge hotfix branch into it
* or create an MR in gitlab, target it towards production (defaults to master) and click merge (needs another approver)

## Check
* Do we need database changes?
* Do we need to restart any services that are not automatically restarted?
* Note that: all services run in docker container in dev environment but on bare machine on production

## Afterwards
* merge your changes back into the master branch: `git checkout master; git merge production` (don't forget to update your local branch before)
* possibly, you get conflicts here to be solved. It is important to do that now and not at the next release, so the person doing the next release is not annoyed :-)
* make the changelog nice again and commit it into the merge commit: `git commit --amend`
