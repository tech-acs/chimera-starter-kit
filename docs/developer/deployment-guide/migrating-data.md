---
---

# Migrating data

When you are developing your dashboard by using tools such as the various make commands, data will be inserted into the postgres database.
When you are ready for deployment, you can use the data-export and data-import commands to migrate the data from your development machine
to the production server.

```
php artisan chimera:data-export
```

Running the above command will prompt you to select which tables from among the relevant ones to include in the export of data. If it does not already exist, it will create a directory named data-export and save one file for each of the files you have selected. 
You can check these file into git so that it becomes available on your production server.

And on your production server, you can run the *data-import* command to import the data you have exported on your development server. If it encounters data 
that has already been inserted, it will be ignored.


```
php artisan chimera:data-import
```

As both of these commands are interactive, you will be fully in-control during the export and import process. 

:::info

In order to use these two commands, you will need to have the *PostgreSQL Client* installed on the system (not PostgreSQL server itself, just the client)

chimera:data-export makes use of **pg_dump** and chimera:data-import uses **psql**

On Ubuntu, you would install PostgreSQL Client like so,

```
sudo apt install postgresql-client
```

:::