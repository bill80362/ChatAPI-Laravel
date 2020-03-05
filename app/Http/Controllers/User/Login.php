<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

use App\User;
use Illuminate\Support\Str;

class Login extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $LoginData = [
            "name"=>$request->json()->get("username"),
            "password"=> $request->json()->get("password"),
        ];
        if (Auth::attempt($LoginData)){
            return response()->json([
                'code' => 200,
                "nick"=> Auth::user()["name"],
                "chat_id"=> Auth::user()["id"],
                "token"=> Auth::user()["api_token"],
                "id"=> Auth::user()["id"],
                "expire"=> Auth::user()["updated_at"],
            ]);
        }else{
            return response()->json([
                'code' => 500,
                'msg' => 'Login Error'
            ]);
        }
    }

    public function register(Request $request){
        if(User::where("name",$request->json()->get("username"))->get()->count()){
            return response()->json([
                'code' => 500,
                'msg' => 'Username is Exist'
            ]);
        }
        $rs = User::Create([
            'name' => $request->json()->get("username"),
            'email' => $request->json()->get("username")."@gmail.com",
            'password' => Hash::make($request->json()->get("password")),
            'api_token' => Str::random(80),
        ]);
        if($rs){
            $LoginData = [
                "name"=>$request->json()->get("username"),
                "password"=> $request->json()->get("password"),
            ];
            Auth::attempt($LoginData);
            return response()->json([
                'code' => 200,
                "nick"=> Auth::user()["name"],
                "chat_id"=> Auth::user()["id"],
                "token"=> Auth::user()["api_token"],
                "id"=> Auth::user()["id"],
                "expire"=> Auth::user()["updated_at"],
            ]);

        }else{
            return response()->json([
                'code' => 500,
                'msg' => 'Rigister Error'
            ]);
        }
    }
}
