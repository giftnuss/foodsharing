# Coding guidelines
<!-- By Peter Tönnies -->
We do have a few coding guidelines, mentioned at various places in these devdocs.
In general, please use your common sense and make it fit with the existing code.

# Code style

## Why

First of all, why do we need a code style:
If the code looks everywhere the same, it is easier to read and thus to maintain.
You are getting kind of a style, a common syntax everybody can understand.
You can even distinguish library code from our own code like this.
There are also some pedantics who like such a clean overall look.
But most important thing is that nobody wants to get reviews saying “too many blank lines”, “please indent by spaces”, “The brace should be on the next line”.
In short, no review on anything regarding white space.

Additionally, we do not want to have to review huge amounts of code with such a diff:

<div align="center"><img src="codestyle-whitespacediff.png" alt="Indentation change diff"></img></div>

if you see this in pure diff style (and are not able to read the matrix without a GUI), this becomes very cumbersome.

## Editorconfig
The cool thing is, as we now all love code styling, but everybody hates code style fixing, we do automate all such things.
The file `/.editorconfig` (see [https://editorconfig.org/]) specifies our most basic settings on white-spacing which most of your IDEs will be able to follow.
[Editorconfig](https://editorconfig.org) is a file format and collection of text editor plugins for maintaining consistent coding styles between different editors and IDEs.

### PHPstorm
In PHPstorm this is how you select it: use the corresponding plugin:

<div align="center"><img src="codestyle-phpstorm-plugin.png" alt="Plugin EditorConfig in PHPstorm"></div>

For the editorconfig file format there are super nice inspections in PHPstorm (under Editor-Inspections after searching for editorconfig). Leave them all in. The more annoying, the better.

