## XHR

XHR ([XMLHttpRequest](https://en.wikipedia.org/wiki/XMLHttpRequest)) is used throughout the project for historic reasons, but should be replaced with modern API endpoints where possible.
So do not implement new features with XHR! The following is just documentation to understand what exists :)

We used XHR for information transferred from the server to the client which is not a complete new page but javascript-initiated.
For example, the Update-Übersicht on the Dashboard was loaded by an XHR that gets a json file with the information of the updates.
The javascript was found in `/client/src/activity.js`, and
it called XHR endpoints like `http://foodsharing.de/xhrapp.php?app=basket&m=infobar`.

This requests an answer by `/xhrapp.php` which in turn calls the correct `php` file based on the options that are given after the `?` in the url.
For example, the `activity.js` requests were answered by
`/src/Modules/Activity/ActivityXhr.php`.
In this example, the database was queried for information via `ActivityModel.php` which in turn used the `/src/Modules/Activity/ActivityGateway.php`.

XHR-request answers contain a status and data and <!-- todo --> ? and always sends the HTTP status 200.
So errors are not recognizable by the HTTP status, but by a custom status in the returned json response.
