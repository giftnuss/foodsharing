## Controllers

Controllers handle requests.
They define how to extract relevant information from the
request, check permissions by calling the correct [`Permission`](php-structure.md#permissions) and calling the suitable [transaction](php-structure.md#transactions).
They define which HTTP response including the response code
is sent back and the conversion of internal data to json
(`return $this->handleView(...)`).

Since the business logic („What is part of an event (= transaction)?“)
is in the transaction classes, a controller method
usually just calls one actual transaction method (apart from permission checks).
It can read necessary information from the session to give those
as arguments to the transaction class.

We have:
- [REST controllers](requests.md#rest-api) with the name `<submodule>RestController.php`
- (legacy) XHR controllers with the name `<module>Xhr.php`
- (legacy) render controllers with the name `<module>Control.php`
- modern render controllers with the name `<module>Controller.php`

Render controllers are called that because they always render a part of the website,
as opposed to API controllers (like REST and XHR),
which are usually called by the rendered website (client) and return data, not an HTML document.

For a guide to refactoring legacy HTML controllers to modern controllers, see the [PHP controller refactoring guide](php-controller-migration.md)
