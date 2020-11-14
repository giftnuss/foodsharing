## Services

Currently, in our source code, some code that assists controllers can be found
in classes named "Service": `/src/Services`.
Some of these classes are Transaction classes that need to be renamed, and some
of them are utility classes. `/src/Services/SanitizerService.php` is the best
example for that.

Some code in the services should rather go into the modules if they
belong to a specific module.

Also see the section [Services](php.md#services) for a broader
use of this term.
