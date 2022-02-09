<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Routing\Exceptions\InvalidSignatureException;
use Illuminate\Session\TokenMismatchException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        $this->renderable(function (InvalidSignatureException $e) {
            return response()->view('error.link-invalid', [], 403);
        });

        $this->renderable(function (Throwable $e) {
            if ($e->getPrevious() instanceof \Illuminate\Session\TokenMismatchException) {
                app('redirect')->setIntendedUrl(url()->previous());
                return redirect()->route('login')
                    ->withInput(request()->except('_token'))
                    ->withErrors('Security token has expired. Please sign-in again.');
            }
        });
    }
}
