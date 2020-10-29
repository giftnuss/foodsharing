# Internationalization

We want the code to be free from German-specific texts to enable non-German speakers to use the site and to make it easier for groups in other countries to use the code.
Unfortunately, we are not quite there yet, but a lot of progress has been made and the remaining hardcoded strings are indicative of obvious legacy code.

We use the Symfony `TranslatorInterface` to replace translation keys (given in code) with their translated values. Translations are read from files in YAML format: for German that would be `/translations/messages.de.yml`.
Aside from those German strings, there are four mail ways in which fixed text on the website can be given in the source code with our translation mechanisms:

- In PHP frontend/view code
Classes derived from Control and View already contain the TranslatorInterface, which is accessible as `$this->translator`, and its `trans()` function that resolves translation keys.  
Other helpers are including it like this:
```
use Symfony\Contracts\Translation\TranslatorInterface;
// instantiate or inject (usually autowired)
$this->translator->trans('fsp.acceptName', ['{name}' => $foodSharePoint['name']])
```
- From JavaScript code
In `client/src/i18n.js` we build a basic replacement function that imports translation resources, replaces variables if needed, and returns the translated value if one exists. Fallbacks are implemented for English and German, which are currently the only languages for which we bundle translations.  
To use this function elsewhere, import the module like so:
```
import i18n from '@/i18n'
pulseInfo(i18n('basket.not_active'))
```
- As part of Vue templates
In `client/src/vue.js` we define the `$i18n` prototype and attach it to all Vue templates. This is the preferred way of frontend translation. If you're translating component parameters, make sure to :bind them accordingly: `:title="$i18n('button.clear_filter')"`
- As part of Twig templates (Legacy)
The [`trans` filter](https://symfony.com/doc/current/translation/templates.html#using-twig-filters) allows passing translation keys to the translation engine from Twig files. Examples:
```
{% raw %}{% embed 'components/field.twig' with {'title': 'events.bread'|trans} %}{% endraw %}
{% raw %}<h4>{{ 'dashboard.push.title'|trans }}</h4>{% endraw %}
```
Do not create more of these unless you really know what you're doing! You will need to be very careful with the syntax for replacing variables (`{% raw %}|trans({'%name%': name}{% endraw %})`).
