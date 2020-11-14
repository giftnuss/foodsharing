## Data Transfer Objects

Currently, domain objects are often represented differently: Some methods receive and return them as associative arrays,
some receive them as a very long list of parameters. If arrays are used, it's often unclear which format the output has
and which format the input is expected to have. Parameter lists on the other hand can get very long, and if parameters
are documented, the documentation for one domain object is spread around the code.

For further structuring  [Data Transfer Objects](https://en.wikipedia.org/wiki/Data_transfer_object) (DTO) can be used.
An example can be found in the Bell module, introduced in the [merge request !1457](https://gitlab.com/foodsharing-dev/foodsharing/-/merge_requests/1457).

DTOs help with clearing up which parameters are expected when and what types they have. DTO classes have public
properties and don't encapsulate logic or functionality. Only logic to create DTOs or convert them from other
representations shall be implemented on the DTO classes as static methods.

As objects are often represented differently, as only parts of them are needed, most domain objects have multiple DTO
representations. That's why we put them in a `DTO` directory inside of the corresponding module directory. Usually,
there is one main or "complete" representation, that includes all aspects of the domain object that can be found in its
database table. This one is just named like the domain object itself (e. g. `Bell`). All other partial represantations
can be named according to their purpose or the place they are used (e. g. `BellForList`).
