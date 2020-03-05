<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\User;
use App\User2User;

class UserController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function Nick(Request $request)
    {
        $Res = $request->json()->all();
        return $request->user();
    }
    public function addFriend(Request $request){
        $f_id = $request->json()->get("fd_map_user_id");
        if (User::find($f_id) ) {
            $is_Exist = User2User::where('id',Auth::user()["id"])->where('f_id',$f_id)->count();
            $rs = null;
            if(!$is_Exist){
                $rs = User2User::create(["id" => Auth::user()["id"], "f_id" => $f_id]);
            }
            if($rs){
                return response()->json([
                    'code' => 200,
                    'fd_map_user_id' => $f_id ,
                ]);
            }else{
                return response()->json([
                    'code' => 500,
                    'msg' => 'Friend ID is Exist' ,
                ]);
            }

        } else {
            return response()->json([
                'code' => 500,
                'msg' => 'Friend ID Error' ,
            ]);
        }
    }
    public function getFriends(Request $request){

        $User2User = User2User::where('id',Auth::user()["id"])->get()->toArray();
        $User2User = array_column($User2User,"f_id");
        $UserList = User::whereIn("id",$User2User)->get()->toArray();
        $data = [];
        foreach ($UserList as $val){
            $data[] = [
                "user_id"=> $val["id"],
                "nick"=> $val["name"],
                "level"=> "M"
            ];
        }
        return response()->json([
            'code' => 200,
            'data' => $data ,
        ]);

    }
    public function delFriend(Request $request){
        $f_id = $request->json()->get("fd_map_user_id");
        if (User::find($f_id) ) {
            $is_Exist = User2User::where('id',Auth::user()["id"])->where('f_id',$f_id)->count();
            $rs = null;
            if($is_Exist){
                $rs = User2User::where('id',Auth::user()["id"])->where('f_id',$f_id)->delete();
            }
            if($rs){
                return response()->json([
                    'code' => 200,
                    'fd_map_user_id' => $f_id ,
                ]);
            }else{
                return response()->json([
                    'code' => 500,
                    'msg' => 'Friend ID is not Exist' ,
                ]);
            }

        } else {
            return response()->json([
                'code' => 500,
                'msg' => 'Friend ID Error' ,
            ]);
        }
    }
    public function getUserbychatid(Request $request){
        $chat_id = $request->json()->get("chat_id");
        dd((int)$chat_id);
        $User = User::find($chat_id);
        return response()->json([
            'code' => 200,
            'data' => $User ,
        ]);
    }
    public function whoaddme(Request $request){
        $User2User = User2User::where('f_id',Auth::user()["id"])->get()->toArray();
        $User2User = array_column($User2User,"id");
        $UserList = User::whereIn("id",$User2User)->get()->toArray();
        $data = [];
        foreach ($UserList as $val){
            $data[] = [
                "user_id"=> $val["id"],
                "nick"=> $val["name"],
                "level"=> "M"
            ];
        }
        return response()->json([
            'code' => 200,
            'data' => $data ,
        ]);
    }




}
