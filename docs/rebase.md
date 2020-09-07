# Contents

* [How you perform a rebase](#how-you-perform-a-rebase)
* [How to deal with rebase conflicts in our dependencies](#how-to-deal-with-rebase-conflicts-in-our-dependencies)
* [Rebase on console with an example](#rebase-on-console-with-an-example)

## How you perform a rebase

1. You get the current changes from the master branch using ``git checkout master`` and ``git pull``.
2. Change back to your branch with ``git checkout BRANCHNAME``.
3. ``git rebase master`` is the command of your choice. (You can find the difference between rebase and merge here: https://git-scm.com/book/en/v2/Git-Branching-Rebasing)

If you use the phpstorm program, click on your branch name below. This will bring up a menu where you select the master and click "checkout and rebase onto current".

4. if the rebase is too complicated or does not work: **Drop it like a hot potato** ;-) with the command ``` "git rebase --abort" ``` Do a merge instead, as this is easier as only the changes from master branch are inserted. You can use the ```git merge master``` command for this.

In phpstorm there are two menu items under the above: "Merge into current". You will find a way to see the version differences in the lower right corner. With the magic wand button at the top you can have conflict-free changes made automatically. "ours" and "theirs" correspond to the arrows on the diff border. (... Someone might insert screenshots here at some point...)

5. After that you can upload the changes with a ```git commit``` and ```git push```, if you manage to fix all conflicts.

## How to deal with rebase conflicts in our dependencies 

(https://stackoverrun.com/de/q/11809185)

If you have made changes to composer.json and / or client/packages.json in your branch and at the same time someone has merged changes to these files into the master, a conflict will occur in composer.lock and yarn.lock.

1. Execute the command ```git checkout master -- chat/yarn.lock```, ... ```client/yarn.lock``` or ... ```composer.lock```
2. Then log into the docker container "client" with ```./scripts/docker-compose run --rm client sh```.
3. Execute the command ```yarn``` and finish with ```exit``` when it is finished. (For composer it would be ```./scripts/composer install```.)
4. Then you can continue the rebase with a ```git add chat/yarn.lock, client/yarn.lock or composer.lock``` and ```git rebase --continue```. 

## Rebase on console with an example

If your rebase in the console looks like this ...

`git rebase master`
```
Auto-merging src/Modules/Profile/ProfileView.php
CONFLICT (content): Merge conflict in src/Modules/Profile/ProfileView.php
Auto-merging lang/DE/de.php
Auto-merging lang/DE/Settings.lang.php
error: could not apply 62feb08c3 ... make use of "about_me_public" as described on profile page.
... then resolve all conflicts manually, mark them as resolved with:
"git add/rm <conflicted_files>", then run "git rebase --continue",
You can instead skip this commit: "run git rebase --skip".
To abort and get back to the state before git rebase, run "git rebase --abort".
Could not apply 62feb08c3 ... make use of "about_me_public" as described on profile page
```
then open the conflicting file with an editor of your choice. It will have the HEAD marker somewhat like this:


```
<<<<<<< HEAD
======= 
/**
     * @param array $infos
     *
     * @return array
     */
    private function renderAboutMePublicInformation(array $infos): array
    {
        if ($this->foodsaver['about_me_public'])
        {
            $infos[] = [
                'name' => $this->translator->trans('foodsaver.about_me_public'),
                'val' => foodsaver['about_me_public'],
            ];
        }
        return $infos;
    }
    /**
     * @param array $infos
     *
     * @return array
     */
     >>>>>>> 62feb08c3... make use of "about_me_public" as described on profile page
```

... change this to what you'd like it to say. Remove the <<< and >>> and === lines.

```
/**
     * @param array $infos
     *
     * @return array
     */
    private function renderAboutMePublicInformation(array $infos): array
    {
        if ($this->foodsaver['about_me_public'])
        {
            $infos[] = [
                'name' => $this->translator->trans('foodsaver.about_me_public'),
                'val' => $this->foodsaver['about_me_public'],
            ];
        }
        return $infos;
    }
    /**
     * @param array $infos
     *
     * @return array
     */
```

Save.
```
git add FILENAME
git rebase --continue
```
