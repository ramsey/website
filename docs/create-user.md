# Create a user account

After [setting up the database](local-database.md), you'll want to create a user
account, so you can log in to the admin:

```shell
./bin/console app:user:create \
    --role=ROLE_ADMIN \
    --role=ROLE_USER \
    --role=ROLE_SUPER_ADMIN \
    "Your Name" \
    you@example.com
```

This will prompt you to enter a password, which will be hashed and stored in the
database.

After that, you can log in at <https://localhost:8000/login> and then go to
<https://localhost:8000/admin>.
