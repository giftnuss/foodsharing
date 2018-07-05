# How to report an issue

Let's suppose that you want to report a simple issue:

 - go to our separate and public [gitlab issues project](https://gitlab.com/foodsharing-dev/issues0/issues)
 - register/sign into gitlab
 - **IMPORTANT:** please search if the issue was reported before. Perhaps try multiple different wordings
 - click on ``New Issue``
 - **IMPORTANT:** choose the appropriate template (bug or feature), throught the menu next to the ``Title`` field
 - **IMPORTANT:** promise now that you will read through the template and then really read through the template :)
 - fill out the template and submit your issue

If you are not sure if the issue is worth reporting or if you have questions [join our slack channel](./resources/links.md): <https://yunity.slack.com/>

# How to fix an issue

You already know how to report an issue and now you want to fix one? Great!
This is very much appreciated! Let's assume that you noticed that the password
reset functionality is not present in the ``Account-Optionen``-menu which is
reachable through ``[Profile Picture] → Einstellungen``.

Being the responsible and awesome developer you are, you searched through the
previous issues and found [issue
61](https://gitlab.com/foodsharing-dev/issues0/issues/61) which proposes to
"Make Password Change easier". Do not bother to create an issue for very small
changes, e.g. doc fixes and typos.

 - check the comments (if there are any). Perhaps someone is already working on this issue or added some useful info
 - (only once:) at this point you *have* to [join our slack channel](./resources/links.md): <https://yunity.slack.com/> so that we can grant you access to the foodsharing source code
 - (only once:) the git repository and follow the [instructions for a local installation]()
 - if your change consists of several smaller commits, create a new designated branch and reference the issue
    - in our case the branch would be named ``61-make-password-change-easier``
    - optionally add your gitlab handle or real name: ``61-inktrap-make-password-change-easier``
 - make the edits *only* in your new branch. Preferably commit in smaller intervals so that we can keep track of the progress and see whats happening
 - ensure the tests pass locally by calling ``./scripts/test``
 - [create a MR](https://docs.gitlab.com/ee/gitlab-basics/add-merge-request.html). If your change requires multiple commits or is bigger, add the ``WIP:`` prefix to the name of the MR
 - wait! somebody will ask you some questions, and if everything goes well, will approve your MR

Congratulations! You just contributed to foodsharing and made a good community better!

