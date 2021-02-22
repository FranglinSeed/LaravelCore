<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\TokenMaster;
use App\Notifications\MailResetPasswordNotification;
use Illuminate\Http\Request;
use App\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AuthController extends Controller
{
  public function register(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'name' => 'required|string|max:255',
      'email' => 'required|string|email|max:255|unique:user_master',
      'password' => 'required|string|min:6|confirmed',
    ]);

    if ($validator->fails()) {
      return response(['error' => $validator->errors()->all()]);
    }

    $request['password'] = hash('md5', $request['password']);
    $request['user_type'] = 1;
    // $request['remember_token'] = Str::random(10);
    $user = User::create($request->toArray());
    $token = $user->createToken('Laravel Password Grant Client')->accessToken;
    $response = ['user' => $user, 'token' => $token];
    return response($response, 200);
  }

  public function fbregister(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'name' => 'required|string|max:255',
      'email' => 'required|string|email|max:255|unique:user_master',
    ]);

    if ($validator->fails()) {
      return response(['error' => $validator->errors()->all()]);
    }

    $user = User::create($request->toArray());
    $token = $user->createToken('Laravel Password Grant Client')->accessToken;
    $response = ['user' => $user, 'token' => $token];
    return response($response, 200);
  }

  public function token_check(Request $request)
  {
    $email = $request->header('email');
    $token = $request->header('Authorization');
    if ($token) {
      $tokenmaster = TokenMaster::where('email', $email)->where('expire', '>', strtotime(date("Y-m-d h:i:sa")))->where('remember', 0)->first();
    } else {
      $tokenmaster = TokenMaster::where('email', $email)->where('expire', '>', strtotime(date("Y-m-d h:i:sa")))->where('remember', 1)->first();
    }

    $passed = 0;
    if ($tokenmaster) {
      $passed = 1;
    }

    $user = User::where('email', $email)->first();
    if ($user) {
      $response = ['user' => $user, 'passed' => $passed];

      return response($response, 200);
    } else {
      $response = ["noUser" => 'User does not exist'];

      return response($response);
    }
  }

  public function login(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'email' => 'required|string|email|max:255',
      'password' => 'required',
    ]);

    if ($validator->fails()) {
      return response(['error' => $validator->errors()->all()]);
    }

    $expire = 1000 * 60 * 60 * 12;

    if ($request->remember) {
      $expire += 1000 * 60 * 60 * 24 * 14;
    }

    $user = User::where('email', $request->email)->first();

    if ($user) {
      if (hash('md5', $request->password) == $user->password) {
        $token = $user->createToken('Laravel Password Grant Client')->accessToken;

        $tokenmaster = new TokenMaster;
        $tokenmaster->email = $request->email;
        $tokenmaster->token = $token;
        $tokenmaster->expire = strtotime(date("Y-m-d h:i:sa")) + $expire;
        $tokenmaster->remember = $request->remember ? 1 : 0;
        $tokenmaster->save();

        $response = ['user' => $user, 'token' => $token];

        return response($response, 200);
      } else {
        $response = ["message" => "Password mismatch"];

        return response($response);
      }
    } else {
      $response = ["message" => 'User does not exist'];

      return response($response);
    }
  }

  public function fblogin(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'email' => 'required|string|email|max:255',
    ]);

    if ($validator->fails()) {
      return response(['error' => $validator->errors()->all()]);
    }

    $expire = 1000 * 60 * 60 * 12;

    if ($request->remember) {
      $expire += 1000 * 60 * 60 * 24 * 14;
    }

    $user = User::where('email', $request->email)->where('facebook_id', $request->id)->first();

    if ($user) {
      $token = $user->createToken('Laravel Password Grant Client')->accessToken;

      $tokenmaster = new TokenMaster;
      $tokenmaster->email = $request->email;
      $tokenmaster->token = $token;
      $tokenmaster->expire = strtotime(date("Y-m-d h:i:sa")) + $expire;
      $tokenmaster->remember = $request->remember ? 1 : 0;
      $tokenmaster->save();
      $response = ['user' => $user, 'token' => $token];

      return response($response, 200);
    } else {
      $response = ["message" => 'User does not exist'];

      return response($response);
    }
  }

  public function logout(Request $request)
  {
    $email = $request->header('email');
    $token = $request->header('Authorization');
    $tokenmaster = TokenMaster::where('email', $email)->where('token', $token)->where('remember', 0)->first();
    if ($tokenmaster) {
      TokenMaster::destroy($tokenmaster->id);
    }

    return response(['msg' => 'Sign out success!']);
  }
}
