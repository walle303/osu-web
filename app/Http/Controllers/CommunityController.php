<?php

/**
 *    Copyright 2015 ppy Pty. Ltd.
 *
 *    This file is part of osu!web. osu!web is distributed with the hope of
 *    attracting more community contributions to the core ecosystem of osu!.
 *
 *    osu!web is free software: you can redistribute it and/or modify
 *    it under the terms of the Affero GNU General Public License version 3
 *    as published by the Free Software Foundation.
 *
 *    osu!web is distributed WITHOUT ANY WARRANTY; without even the implied
 *    warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *    See the GNU Affero General Public License for more details.
 *
 *    You should have received a copy of the GNU Affero General Public License
 *    along with osu!web.  If not, see <http://www.gnu.org/licenses/>.
 */
namespace App\Http\Controllers;

use Cache;
use Auth;
use Redirect;

use Illuminate\Http\Request;

class CommunityController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Community Controller
    |--------------------------------------------------------------------------
    |
    | Frontend to the community of osu!
    | Unsure if forum should use /forum or not.
    | Route:
    |
    |	Route::get('/community</page>', 'CommunityController@get<Page>');
    |
    */
    protected $section = 'community';

    public function getChat()
    {
        return view('community.chat');
    }

    public function getLive(Request $request)
    {
        $streams = null;
        $featuredStream = null;
        $streams = Cache::remember('livestreams', 300, function() {
            $justin_api_url = "https://api.twitch.tv/kraken/streams?on_site=1&limit=40&offset=0&game=Osu!";
            $data = json_decode(file_get_contents($justin_api_url));
            $streams = $data->streams;
            return $streams;
        });

        if (Cache::has("featuredStream")) {
            $featuredStreamId = Cache::get("featuredStream");
            foreach ($streams as $stream) {
                if ($stream->_id == $featuredStreamId) {
                    $featuredStream = $stream;
                }
            }
        }
        return view('community.live', compact("streams", 'featuredStream'));
    }
    public function postLive(Request $request)
    {
        if (Auth::user() != null && Auth::user()->isGmt()) { 
            if ($request->has("promote")) 
                Cache::forever("featuredStream", $request->promote);

            if ($request->has("unpromote")) 
                Cache::forget("featuredStream");
        }
        return Redirect::back();
    }
}
