<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserController extends Controller
{
    //

   public function index(){
       return view('orders.index');
   }
   public function settings(){
       return view('orders.settings');
   }
//    public function settings(){
//        return view('orders.setting');
//    }
}