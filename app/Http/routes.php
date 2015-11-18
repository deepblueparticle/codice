<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', ['as' => 'index', 'uses' => 'NoteController@getIndex']);

Route::get('create', ['as' => 'note.create', 'uses' => 'NoteController@getCreate']);
Route::post('create', 'NoteController@postCreate');

Route::get('login', ['as' => 'user.login', 'uses' => 'UserController@getLogin']);
Route::post('login', 'UserController@postLogin');
Route::get('logout', ['as' => 'user.logout', 'uses' => 'UserController@getLogout']);

Route::get('note/{id}/edit', ['as' => 'note.edit', 'uses' => 'NoteController@getEdit']);
Route::post('note/{id}/edit', 'NoteController@postEdit');
Route::get('note/{id}/mark', ['as' => 'note.change', 'uses' => 'NoteController@getChangeStatus']);
Route::get('note/{id}/remove', ['as' => 'note.remove', 'uses' => 'NoteController@getRemove']);
