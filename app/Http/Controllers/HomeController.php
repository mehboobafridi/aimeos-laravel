<?php

namespace App\Http\Controllers;

use App\Models\Region;
use App\Models\Subscriber;
use Auth;
use Illuminate\Http\Request;
use SimpleXMLElement;
use Yajra\DataTables;
use App\Models\Site;

class HomeController extends Controller
{
    public function home(Request $request)
    {

        try {
            
            $sites = Site::all();

 

            return view('index', compact('sites'));
        //code...
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function index(Request $request)
    {
        if (view()->exists($request->path())) {
            return view($request->path());
        }
        return view('pages-404');
    }


}
