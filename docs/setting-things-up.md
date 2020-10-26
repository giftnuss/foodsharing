# Setting things up

You must have completed the [installation](./running-the-code.md) setup before doing this.

Now go and visit [localhost:18080](http://localhost:18080) in your browser.
You should see a foodsharing instance running on your local machine :)

For generating a bit of initial data to play with, execute the seeding script:

```
./scripts/seed
```

It will give you some users that you can log in with:

| Email                       | Password | Role          |
|-----------------------------|----------|---------------|
| `user1@example.com`         | `user`   | Foodsharer    |
| `user2@example.com`         | `user`   | Foodsaver     |
| `storemanager1@example.com` | `user`   | Store manager |
| `storemanager2@example.com` | `user`   | Store manager |
| `userbot@example.com`       | `user`   | Ambassador    |
| `userbot2@example.com`      | `user`   | Ambassador    |
| `userbotreg2@example.com`   | `user`   | Ambassador    |
| `userorga@example.com`      | `user`   | Orgateam      |

Please refer to the [User Roles and Permissions](#learn-user-roles.md) section for details on the different roles.  
*Tip: You can use private browser windows to log in with multiple users at the same time!*

The script also generates more dummy users and dummy data to fill the page with life (a bit at least).
Should you want to modify it, have a look at the file `/src/Dev/SeedCommand.php`.

Whenever you make changes to non-PHP frontend files (e.g. .vue, .js or .scss files), those are directly reflected in the running docker instance. Changes to PHP files will require a page reload in your browser.

To stop everything again, just run:

```
./scripts/stop
```

PHPMyAdmin is also included: [localhost:18081](http://localhost:18081). Log in with:

| Field    | Value |
|----------|-------|
| Server   | db    |
| Username | root  |
| Password | root  |

There you can directly look at and manipulate the data in the database
which can be necessary or very useful for manual testing and troubleshooting.

MailDev is also included: [localhost:18084](localhost:18084). There you can read all e-mails that you write via the front end.

{% include "./ide-setup.md" %}
