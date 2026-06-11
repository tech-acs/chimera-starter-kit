---
---

# Linking storage directory

Files are by default stored in the *storage/app* directory. This prevents files from been publicly accessible (that is, anyone assessing your files over the internet without needing permission).

So to display files in our application from the storage directory correctly, we will create a symbolic link to the public directory using the following artisan command:

```
php artisan storage:link
```

For example, profile photos are stored in the storage directory and unless you have created the symbolic link, profile pictures will not be visible.

Please refer to the [relevant section](https://laravel.com/docs/9.x/filesystem#the-public-disk) of the Laravel documentation to read more about it.