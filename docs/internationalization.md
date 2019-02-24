# Internationalization

We want the code to be free from German-specific texts to enable non-German speakers to use the site and to make it easier for groups in other countries to use the code.
Unfortunately, we are not there yet. At the moment there are three ways how fixed text on the website is given in the source code:

- Hardcoded text (obviously legacy code), e.g.
```
$this->func->addContent('<...>Updates-Übersicht<...>')
```
Do not create more of this hardcoded German!
- References to the translation files in `/lang/DE/....php` via the function `s` in `/src/Lib/Func.php`, e.g.
```
$this->func->s('close_foodbaskets')
```
- References to the translation file `/lang/lang.de.yml`.
TODO: describe how to use this data. It has something to do with symfony.
This is the way to go when you create new text on the website.
