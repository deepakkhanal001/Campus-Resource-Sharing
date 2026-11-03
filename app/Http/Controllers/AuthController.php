<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Auth\RegisteredUserController;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function __construct(private readonly RegisteredUserController $registration) {}

    public function showRegister(): View
    {
        return $this->registration->create();
    }

    public function register(Request $request)
    {
        return $this->registration->store($request);
    }
}
