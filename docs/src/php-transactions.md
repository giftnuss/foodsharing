## Transactions

All modules have certain business rules/domain logic to follow when their data is modified. After all, there are always
certain operations that have to be executed together to ensure that the data keeps being consistent according to the
the rules that apply to them in reality. We implement these transactions of operations executed together as methods on
Transaction classes.

For example, when someone wants to join a store pickup, it's not enough to just insert this information into the
database. We also have to be check if the user has the rights to join without a confirmation, and if not, we have to
make sure that the store owner gets notified that they should confirm or deny it.

This is why joining a pickup is implemented in the `joinPickup()` method on the corresponding Transaction class. All
controllers should use this transaction if they want to make a user join a pickup, because only if all steps of the
transaction are executed, the pickup joining is complete.

What should not be part of a transaction class:

* knowledge of the underlying database (should still work with a gateway reading from punched cards)
* knowledge of request types (e.g. should be callable from a desktop application or some different internet protocol). Therefore transaction classes do not raise HTTPException or choose HTTP response codes or the json representation of responses
* the session - but at this point we are not strict, so far transaction classes use information of the session

### Permissions

Permission classes are used to organize what actions are allowed for which user.
They are a special type of transaction class.
