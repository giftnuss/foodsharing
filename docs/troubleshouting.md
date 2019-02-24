# Troubleshouting

During development everyone knows the moments when the code shows exactly what you want but the computer just does something different.
Some strategies how to find or avoid errors are collected here.

## CSRF Exception

When working on the api one usually wants to try it out.
If you just type in the api call in the web browser while running the local webpage on [`localhost:18080`](setting-things-up.md) you probably get a [`CSRF Exception`](https://de.wikipedia.org/wiki/Cross-Site-Request-Forgery).
This is a safety feature:
- While you are logined via foodsharing.de other pages can send api calls.
- Since your browser has a session foodsharing.de usually would answer the request, the other page got data that it shouldn't get.
- Solution: foodsharing.de sends a csrf-token that the client saves as a cookie and sends with every api call. Since cookies can only be accessed by the correct web page, only the foodsharing.de-Tab can authenticate itself.
- When you just type in the api call you look like a different site/ tab and get rejected.

There are several work-arounds:
- You write tests. You should write tests anyway and since they emulate a complete session, the CSRF-Token is sent and valid.
- You add an api call in some javascript-file that gets executed. For example add the following into `/src/Modules/Dashboard/Dashboard.js`:
```
import { get } from '@/api/base'
get('/activity')
```
Make sure that you do not commit those temporary changes!
- You disable the SCRF-Check in `/src/EventListener/CsrfListener.php` by commenting the lines
```
// if (!$this->session->isValidCsrfHeader()) {
//  throw new SuspiciousOperationException('CSRF Failed: CSRF token missing or incorrect.');
//}
```
Make sure that you do not commit those temporary changes!
