<?php

namespace Codice\Http\Controllers;

use Codice\Codice;
use Codice\Label;
use Codice\Note;
use Redirect;
use View;

class InfoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display "About Codice" page.
     *
     * @return \Illuminate\Http\Response
     */
    public function getAbout()
    {
        $changelog = @file_get_contents('http://codice.eu/changelog.txt');
        if ($changelog == false) {
            $changelog = trans('info.about.changelog-error');
        }

        return View::make('info.about', [
            'changelog' => $changelog,
            'title' => trans('info.about.title'),
            'version' => (new Codice)->getVersion(),
        ]);
    }

    /**
     * Check for software updates.
     *
     * @return \Illuminate\Http\Response
     */
    public function getUpdates()
    {
        $version = @file_get_contents('http://codice.eu/version.txt');
        if ($version == false) {
            return Redirect::route('about')->with([
                'message' => trans('info.updates.error'),
                'message_type' => 'danger',
            ]);
        }

        $version = trim($version);

        if (version_compare($version, (new Codice)->getVersion(), 'gt')) {
            return Redirect::route('about')->with([
                'message' => trans('info.updates.available', ['version' => $version]),
                'message_type' => 'info',
                'message_raw' => true,
            ]);
        } else {
            return Redirect::route('about')->with([
                'message' => trans('info.updates.none'),
                'message_type' => 'success',
            ]);
        }
    }

    /**
     * Display statistics.
     *
     * @return \Illuminate\Http\Response
     */
    public function getStats()
    {
        $stats = [
            'all' => Note::mine()->count(),
            'done' => Note::whereStatus(1)->mine()->count(),
            'pending' => Note::whereStatus(0)->whereRaw('expires_at > NOW()')->mine()->count(),
            'expired' => Note::whereStatus(0)->whereRaw('expires_at < NOW()')->mine()->count(),
            'labels' => Label::mine()->count(),
            'notes_by_label' => '<a href="' . route('labels') . '">' . trans('info.stats.show') . '</a>',
        ];

        return View::make('info.stats', [
            'stats' => $stats,
            'title' => trans('info.stats.title'),
        ]);
    }
}
