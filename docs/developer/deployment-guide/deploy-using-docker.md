---
---

# Deploy using docker

The starter kit provides a Docker environment for your dashboard. Other than Docker, no software or libraries are required to be installed on your machine. After chimera package has been installed, you may run the chimera:dockerize artisan command. This command will publish a docker-compose.yml file and runtime config files to the root of your application

```bash
php artisan chimera:dockerize
```
To Build the dashboard container image use:

```bash
docker-compose build
```

Finally, you may start docker containers using:

```bash
docker-compose up
```

you can run the php artisan migrate command using this command

```bash
docker-compose exec chimera.web php artisan migrate
```
you can run the adminify command to create a super admin user with which you can access your new dashboard

```bash
docker-compose exec chimera.web php artisan adminify
```