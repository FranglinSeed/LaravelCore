<?php

namespace App\Http\Controllers\Password;

use App\Http\Controllers\Controller;
use App\Mail\ResetPassMail;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use App\User;
use App\Models\PasswordReset;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class PasswordResetController extends Controller
{
  public function forgot(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'email' => 'required|string|email|max:255',
    ]);

    if ($validator->fails()) {
      return response(['error' => $validator->errors()->all()]);
    }

    $user = User::where('email', $request->email)->first();
    if (!$user)
      return response([
        'message' => "We can't find a user with that e-mail address.",
        "type" => "error"
      ]);

    $passwordReset = PasswordReset::updateOrCreate(
      ['email' => $user->email],
      [
        'email' => $user->email,
        'token' => Str::random(60)
      ]
    );

    Mail::to($user)->send(new ResetPassMail($user->name, $passwordReset->token));

    return response(
      [
        'message' => 'If a valid email address was entered, instructions to reset your password have been sent to this email address.',
        "type" => "success"
      ]
    );
  }
  /**
   * Find token password reset
   *
   * @param  [string] $token
   * @return [string] message
   * @return [json] passwordReset object
   */
  public function find($token)
  {
    $passwordReset = PasswordReset::where('token', $token)
      ->first();

    if (!$passwordReset)
      return response()->json([
        'error' => 'This password reset token is invalid.',
      ]);

    if (Carbon::parse($passwordReset->updated_at)->addMinutes(720)->isPast()) {
      $passwordReset->delete();

      return response()->json([
        'error' => 'This password reset token is invalid.',
      ]);
    }

    return response()->json($passwordReset);
  }
  /**
   * Reset password
   *
   * @param  [string] email
   * @param  [string] password
   * @param  [string] password_confirmation
   * @param  [string] token
   * @return [string] message
   * @return [json] user object
   */
  public function reset(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'email' => 'required|string|email',
      'password' => 'required|string|min:6|confirmed',
      'token' => 'required|string'
    ]);

    if ($validator->fails()) {
      return response(['error' => $validator->errors()->all()]);
    }

    $passwordReset = PasswordReset::where([
      ['token', $request->token],
      ['email', $request->email]
    ])->first();

    if (!$passwordReset)
      return response()->json([
        'error' => ['This password reset token is invalid.']
      ]);

    $user = User::where('email', $passwordReset->email)->first();
    if (!$user)
      return response()->json([
        'error' => ["We can't find a user with that e-mail address."]
      ]);

    $user->password = hash('md5', $request->password);
    $user->save();
    $passwordReset->delete();
    return response(['user' => $user]);
  }
}
