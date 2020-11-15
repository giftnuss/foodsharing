## Syntax

Here we collect the parts of the [PHP](https://secure.php.net/docs.php) language that we use in this project.
As I write this I already got some experiences in other languages and therefore only include short explanations of surprising syntax.

- Variables: all occurences of variables are starting with a Dollar (`$`) symbol:
```
$out = '<ul class="linklist baskets">';
```
- `=` is an assignment
- `'string'` and `"string"` are strings. Variables inside double-quoted string literals get replaced by their values.
- `.` is the concatenation operator for strings (in other languages `+`)
- `.=` adds the string to the right to the variable on the left
- `$var->member` refers to a member variable or method of an object (in other languages `.`)
- [`ClassName::member`](https://secure.php.net/manual/de/language.oop5.paamayim-nekudotayim.php) refers to a (static) member variable or function of a _class_
- `...` is used for packing and unpacking arrays in argument lists
  (packing in function definition argument list, unpacking in
  function use argument list). Longer explanation on [stackoverflow](https://stackoverflow.com/a/57992172)

## Conventions

- Do not use global variables (those indicated by the keyword `global`, can be found in legacy code)
- Indentation: we use one tabstop per indentation level
- Variables in strings: we do not use variables in strings but concatenate:
```
$list = 'bla'
$list .= $list . 'some text' . $someVariable . 'end text'
```
- When (re)writing php code add types in arguments and return types of functions. If you work with existing code it is not always trivial and is therefore not enforced. In general:
  -  as a parameter, something should be either the type (e.g. `string`, `int`) OR `null` if okay to not be set. `?string $blub = null` works as a parameter definition to allow that.
  - as a return type, something should always ever be one type, if possible. Throw an exception otherwise. Especially an empty array `[]` is fine to say that there is no data when otherwise data would have been returned as an array.
