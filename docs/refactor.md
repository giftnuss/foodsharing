# Refactoring

We want to continue refactoring the codebase. This will lead to code which is more modular, easier to maintain and clearer. 
A lot is explained in the book "Modernizing Legacy Applications In PHP" (mentioned [here](codebase.md#basic-layout)  in more detail)

There is an [overview issue](https://gitlab.com/foodsharing-dev/foodsharing/-/issues/68) with a checklist.

If you have an idea for a new refactoring concept, please think, before implementing, if this would increase the work for others :). 


## Front-end

Currently, we use three different ways for the front-end code.

The **oldest** one is in the View files, in which php functions build the HTML and JavaScript code of the website.

If you refactor something or want to make something **new**, please use [vue.js](javascript.md)

There are also **twig** templates used.

It is not necessary to refactor these solely for the purpose of refactoring, but if you try to fix something in an old file, it would be great to **refactor it to vue.js** then. If you are making a new front-end page, you should definitivly use vue.js.


## Network

Request handling is explained in the [here](requests.md) and more about Rest API endpoints can be found [here](code-review.md#rest-api-endpoints).


## Back-End

The back-end code is structured like this:

| Class       | Purpose              | Comment                        |
| --------    | --------             | --------                       |
| Control     | Action Logic         | Things that are neither database interaction nor presentation logic      | 
| Permission  | Permission Checks    | What kind of user may do what? |
| Service     | ?                    |            |
| Helper      | ?                    |            |



A bit more explanation can be found [here](php.md).

Helper classes could be used to put some functions unorganized there. Please think about if there might not be a better class to put it. Some parts might be better in Service classes. (If in doubt, please ask the team.)


## Database Access

Currently there are SQL statements in ```*Model``` classes, in XhrMethods and in ```*Gateway``` classes.

[Issue #9](https://gitlab.com/foodsharing-dev/foodsharing/-/issues/9)  tracks the refactor procress of moving everything to ```*Gateway``` classes. 


## Data Transfer Object (DTO)

There are different array representations used in the foodsharing code, sometimes even describing the same data. DTO classes can be used to bring more structure into this. They also help with **clearing up which parameters are expected when and what types they have**. 

For data conversion from the database into arrays, there can be static functions used.

There should be a uniform naming convention for them. Currently we don't have one. An example can be found in the Bell module, currently in this [merge request !1457](https://gitlab.com/foodsharing-dev/foodsharing/-/merge_requests/1457). If there is agreement on them, please add it here.

