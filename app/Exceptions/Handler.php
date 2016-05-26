<?php

namespace Codice\Exceptions;

use App;
use Auth;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Session\TokenMismatchException;
use Redirect;
use Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        HttpException::class,
        ModelNotFoundException::class,
        LabelNotFoundException::class,
        NoteNotFoundException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $e
     * @return void
     */
    public function report(Exception $e)
    {
        return parent::report($e);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $e
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $e)
    {
        if ($e instanceof HttpException) {
            $code = $e->getStatusCode();

            if (Auth::check()) {
                $view = 'authorized';
            } else {
                $view = 'unauthorized';
                // We don't know language of the visitor so let's provide English version
                App::setLocale('en');
            }

            return Response::view("error.$view", [
                'error' => "HTTP $code",
                'message' => trans("app.error.http.$code"),
                'title' => "HTTP $code",
            ], $code);
        }

        if ($e instanceof TokenMismatchException) {
            return Redirect::back()->with('message', trans('app.error.token-mismatch'))
                ->with('message_type', 'danger')
                ->withInput();
        }

        if ($e instanceof LabelNotFoundException) {
            return Redirect::route('index')->with('message', trans('labels.not-found'))
                ->with('message_type', 'danger');
        }

        if ($e instanceof NoteNotFoundException) {
            return Redirect::route('index')->with('message', trans('note.not-found'))
                ->with('message_type', 'danger');
        }

        if ($e instanceof ModelNotFoundException) {
            $e = new NotFoundHttpException($e->getMessage(), $e);
        }

        return parent::render($request, $e);
    }
}
