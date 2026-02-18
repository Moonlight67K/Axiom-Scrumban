<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index() {
        return view('dashboard');
    }

    public function taskAssignment() {
        return view('taskassignment');
    }

    public function kanbanTeams() {
        return view('kanbanteams');
    }

    public function taskProgress() {
        return view('taskprogress');
    }

    public function reminders() {
        return view('reminders');
    }
}
