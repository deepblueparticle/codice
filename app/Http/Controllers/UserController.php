<?php

namespace Codice\Http\Controllers;

use Auth;
use Codice\User;
use Hash;
use Input;
use Redirect;
use Session;
use Validator;
use View;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest', ['only' => 'getLogin']);
        $this->middleware('auth', ['except' => ['getLogin', 'postLogin']]);
    }

    /**
     * Display a login form.
     *
     * @return \Illuminate\Http\Response
     */
    public function getLogin()
    {
        return View::make('user.login');
    }

    /**
     * Processes a login form.
     *
     * @return \Illuminate\Http\Response
     */
    public function postLogin()
    {
        $credentials = [
            'email' => Input::get('email'),
            'password' => Input::get('password')
        ];

        $validator = Validator::make($credentials, [
            'email' => 'email|required',
            'password' => 'required',
        ]);

        if ($validator->passes()) {
            if (Auth::attempt($credentials)) {
                if (Session::has('url.intended')) {
                    return Redirect::to(Session::get('url.intended'));
                }

                return Redirect::route('index');
            }

            return Redirect::back()->with('message', trans('user.login.invalid'));
        } else {
            return Redirect::back()->withErrors($validator)->withInput();
        }
    }

    /**
     * Logs user out
     *
     * @return \Illuminate\Http\Response
     */
    public function getLogout()
    {
        Auth::logout();
        return Redirect::route('user.login');
    }

    /**
     * Displays settings section for current user.
     *
     * @return \Illuminate\Http\Response
     */
    public function getSettings()
    {
        return View::make('user.settings', [
            'title' => trans('user.settings.title'),
            'user' => Auth::user(),
        ]);
    }

    /**
     * Processes settings form.
     *
     * @return \Illuminate\Http\Response
     */
    public function postSettings()
    {
        $validator = Validator::make(Input::all(), [
            'email' => 'required|email|unique:users,email,' . Auth::id(),
            // FIXME: regex would be fine, but what about other i18n support?
            //'options.phone' => 'numeric',
            'password_new' => 'confirmed',
            'options.notes_per_page' => 'numeric'
        ]);

        if ($validator->passes()) {
            $message = trans('user.settings.success');

            $user = Auth::user();
            if (Input::has('password') && Input::has('password_new')) {
                if (!Hash::check(Input::get('password'), $user->password)) {
                    return Redirect::back()->with('message', trans('user.settings.password-wrong'))
                        ->with('message_type', 'danger');
                }

                $user->password = bcrypt(Input::get('password_new'));
                $message = trans('user.settings.success-password');
            }
            $user->email = Input::get('email');
            $user->options = Input::get('options');
            $user->save();

            return Redirect::back()->with('message', $message);
        } else {
            return Redirect::back()->withErrors($validator)->withInput();
        }
    }
}
