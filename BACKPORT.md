# Backporting fixes and features to old versions of dashboard


## Fix "page expired" error

Add the following code to the register() method of the Handler class in app/Exceptions/Handler.php file

```php
$this->renderable(function (Throwable $e) {
    if ($e->getPrevious() instanceof \Illuminate\Session\TokenMismatchException) {
        app('redirect')->setIntendedUrl(url()->previous());
        return redirect()->route('login')
            ->withInput(request()->except('_token'))
            ->withErrors('Security token has expired. Please sign-in again.');
    }
});
```