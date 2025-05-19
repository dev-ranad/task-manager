<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Traits\Response;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    use Response;

    public function register(Request $request)
    {
        DB::beginTransaction();
        try {
            if (Auth::check()) {
                return response()->json([
                    'message' => 'You are already logged in.',
                ], 403);
            }

            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string|min:8|confirmed',
            ]);

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            DB::commit();

            return $this->successResponse('Registration successful.', null, 200);
        } catch (ValidationException $e) {
            DB::rollBack();
            return $this->errorResponse(
                $this->responseMessage('Registration', 'validation'),
                $e->validator->errors()->messages()
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Registration failed. ' . $e->getMessage() . '.', [], 500);
        }

    }

    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ]);

            $user = User::where('email', $request->email)->first();

            if (! $user || ! Hash::check($request->password, $user->password)) {
                return $this->errorResponse('Invalid credentials.', [], 401);
            }

            $token = $user->createToken('access_token', ['*'], Carbon::now()->addDays(7));

            $credential = [
                $token,
                'token_type' => 'Bearer',
                'user' => $user,
            ];
            return $this->successResponse('Login successful.', $credential);
        } catch (ValidationException $e) {
            DB::rollBack();
            return $this->errorResponse(
                $this->responseMessage('Login', 'validation'),
                $e->validator->errors()->messages()
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Login failed. ' . $e->getMessage() . '.', [], 500);
        }
    }

    public function logout(Request $request)
    {
        try {
            $request->user()->tokens()->delete();

            return $this->successResponse('Logout successful.', null);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Logout failed. Please try again.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
