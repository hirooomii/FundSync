<?php

namespace App\Http\Controllers;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Inertia\Inertia;
use Carbon\Carbon;

class ReconcilliationController extends Controller
{
    public function index()
    {
        return Inertia::render('Reconcilliation/Reconcilliation');
    }
}
