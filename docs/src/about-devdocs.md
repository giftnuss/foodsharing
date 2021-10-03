# About the devdocs

Feel free to provide feedback or ask questions at the #foodsharing-dev [Slack](https://slackin.yunity.org/) channel at any time.

## General information

The developer documentation (devdocs) contains general information about the foodsharing website project, step-by-step instructions, and references.

## Target groups of the devdocs

The devdocs should offer everything newcomers need to start participating in the foodsharing website project.

The devdocs should also be of help to anyone that got stuck while working on the foodsharing website project and is in need of help.

## Contributing to the devdocs

Anyone can contribute to the devdocs. The git project directory is called `docs`.

The devdocs are based on the following principles:

- Information should be correct and current
- Information should be consistent (language, formatting,...)
- Information should be concise (but some repetition is necessary)
- Information should be complete (probably an infeasible ideal)

But don't worry too much about the last three principles.
<!-- There are people solely dedicated to improving the devdocs. -->

### What in, what out?

How do I decide if a specific information should be in the devdocs?
Here are some principles:
- Is the information specific to foodsharing? -> Yes
- Were you surprised about it? -> Yes
- Is the information general enough to be explained in a foodsharing-independent tutorial or documentation? -> No, include a link
- Is the information specificly about one piece of code and only interesting during reading this code (and not for finding this piece of code)? -> No, explain the code with comments in the code.

### Markdown

The devdocs are written in [Markdown](https://docs.gitbook.com/editing-content/markdown) (md).

For more info please see [Documentation](/documentation.html).

### Local setup

The current devdocs available via [`localhost:3000`](http://localhost:3000) in your browser.

The devdocs are built at every change on the master branch and published [here](https://devdocs.foodsharing.network).

The gitlab ci is not triggered if you push with the option `git push -o ci.skip`.
This is useful if you work on the devdocs since they are only built on master anyway.

## mdbook

The devdocs are built with [mdbook](https://rust-lang.github.io/mdBook/index.html).
