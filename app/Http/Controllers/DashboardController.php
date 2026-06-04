<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Contract;
use App\Models\Project;

class DashboardController extends Controller
{
    public function index()
    {
        return view('dashboard', [
            'companyCount'  => Company::count(),
            'projectCount'  => Project::count(),
            'contractCount' => Contract::count(),
        ]);
    }
}
