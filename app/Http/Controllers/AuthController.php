<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use App\Services\AuthService;
use Illuminate\Http\Request;

class AuthController extends Controller
{
	public function __construct(
		protected AuthService $authService
	) {}

	public function register(RegisterRequest $request)
	{
		$result = $this->authService->register($request->validated());

		return response()->json($result);
	}

	public function login(LoginRequest $request)
	{
		$result = $this->authService->login($request->validated());

		return response()->json($result);
	}

	public function changePassword(ChangePasswordRequest $request)
	{
		$result = $this->authService->changePassword($request->user(), $request->validated());

		return response()->json($result);
	}

	public function user(Request $request)
	{
		return response()->json($request->user());
	}

	public function logout(Request $request)
	{
		$request->user()->currentAccessToken()->delete();

		return response()->json(['message' => 'Logged out']);
	}
}
