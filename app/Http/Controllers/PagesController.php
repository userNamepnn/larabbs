<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PagesController extends Controller
{
    function root()
    {
        return view('pages.root');
    }

    public function permissionDenied()
    {
        if (config('administrator.permission')()){
            return redirect(url(config('administrator.uri')), '302');
        }
        return view('Pages.permission_denied');
    }
}
