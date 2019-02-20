# About the devdocs

Feel free to provide feedback or ask questions at the #foodsharing-dev [Slack](https://slackin.yunity.org/) channel at any time.

## General information

The developer documentation (devdocs) contains general information about the foodsharing website project, step-by-step instructions, and references.

## Target groups of the devdocs

The devdocs should offer everything newcomers need to start participating in the foodsharing website project.

The devdocs should also be of help to anyone that got stuck while working on the foodsharing website project and is in need of help.

## Contributing to the devdocs

Anyone can contribute to the devdocs. The git project folder is called `docs`.

The devdocs are based on the following principles:

- Information should be correct and current
- Information should be consistent (language, formatting,...)
- Information should be concise (but some repetition is necessary)
- Information should be complete (probably an infeasible ideal)

But don't worry too much about the last three principles. There are people solely dedicated to improving the devdocs.

### What in, what out?

How do I decide if a specific information should be in the devdocs?
Here are some principles:
- Is the information specific to foodsharing? -> Yes
- Were you surprised about it? -> Yes
- Is the information general enough to be explained in a foodsharing-independent tutorial or documentation? -> No, include a link
- Is the information specificly about one piece of code and only interesting during reading this code (and not for finding this piece of code)? -> No, explain the code with comments in the code.
### Setting things up

The devdocs are built at every change on the master branch and published [here](https://devdocs.foodsharing.de).
To see your changes, you can build the devdocs locally.
You need to have `yarn` installed on your system.
Run
```
yarn global add gitbook-cli
gitbook install docs
gitbook serve docs --port 4001
```
This makes the current devdocs avaiable via `localhost:4001` in your browser.
It is updated at every change of the files.
It would be nice to have a docker setup (to avoid the local `yarn` dependency). Please document it here if you set it up.

## GitBook

The devdocs are based on [GitBook](https://docs.gitbook.com/).
