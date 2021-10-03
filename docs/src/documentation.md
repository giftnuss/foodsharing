# Documentation <!-- omit in toc -->

The key words "MUST", "MUST NOT", "REQUIRED", "SHALL", "SHALL NOT", "SHOULD", "SHOULD NOT", "RECOMMENDED", "MAY", and "OPTIONAL" in this document are to be interpreted as described in [RFC 2119](https://datatracker.ietf.org/doc/html/rfc2119).

- [Overview](#overview)
- [General](#general)
  - [Files](#files)
- [Linting](#linting)
  - [Using Docker](#using-docker)
  - [Editor Extensions](#editor-extensions)

## Overview

Documentation should be as important to a developer as all other facets of development.
Or as Atlassian states it: [Documentation should be your best friend](https://www.atlassian.com/work-management/documentation/importance-of-documentation).
Therefore it is recommended to embrace this attitute going forward.

This document should help you with the basics and guidelines of documentation for this project.
For the developer documentation please check out the section [about the devdocs](/about-devdocs.html).

## General

You may always consider using online guides like <https://www.markdownguide.org/> or <https://guides.github.com/features/mastering-markdown/> (besides GitHub Flavored Markdown for the latter which only works on GitHub.com of course).

### Files

All Markdown files MUST have `.md` file extension.

All Markdown files MUST use the Unix LF (linefeed) line ending only.

All Markdown files SHOULD end with a non-blank line, terminated with a single LF.

All Markdown files SHOULD be linted. (See [Linting](#linting))

All Markdown files SHOULD contain a table of contents. (For auto generation check out the [editing recommendations](#editing).)

## Linting

See the general [rules of markdownlint](https://github.com/DavidAnson/markdownlint/blob/main/doc/Rules.md).
Custom rules are applied using a config file with the name [.markdownlint.json](https://gitlab.com/foodsharing-dev/foodsharing/-/blob/master/.markdownlint.json).

### Using Docker

Use [scripts/lint-markdown](/scripts.html#script-overview).

### Editor Extensions

Please see the [related to markdownlint](https://github.com/DavidAnson/markdownlint#related) section.
