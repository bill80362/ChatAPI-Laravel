<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\User;
use App\Room;
use App\Room2User;

class RoomController extends Controller
{
    public function create(Request $request)
    {
        $user_id = $request->json()->get("map_user_id");
        $room_group = $request->json()->get("room_group");
        $room_name = $request->json()->get("room_name");
        //儲存
        $Room = new Room();
        $Room->user_id = $user_id;
        $Room->RoomType = $room_group;
        $Room->RoomName = $room_name;
        $rs = $Room->save();
        //回應
        if($rs){
            return response()->json([
                'code' => 200,
                'room_id' => $Room->id,
                'user_id' => (int)$Room->user_id,
                'room_group' => $Room->RoomType,
                'room_name' => $Room->RoomName,
            ]);
        }else{
            return response()->json([
                'code' => 500,
                'msg' => 'create room error' ,
            ]);
        }
    }
    public function update(Request $request){
        $room_id = $request->json()->get("room_id");
        $room_name = $request->json()->get("room_name");
        //儲存
        $Room = Room::find($room_id);
        $Room->RoomName = $room_name;
        $rs = $Room->save();
        //回應
        if($rs){
            return response()->json([
                'code' => 200,
                'room_id' => $Room->id,
                'user_id' => (int)$Room->user_id,
                'room_group' => $Room->RoomType,
                'room_name' => $Room->RoomName,
            ]);
        }else{
            return response()->json([
                'code' => 500,
                'msg' => 'create room error' ,
            ]);
        }
    }
    public function getRoomList(){
        $RoomIDList = Room2User::where('user_id', Auth::user()["id"])->get()->toArray();
        $RoomIDList = array_column($RoomIDList,"id");
        $RoomList = Room::whereIn('id', $RoomIDList)->get()->toArray();
        //回應
        return response()->json([
            'code' => 200,
            'data' => $RoomList,
        ]);
    }
    public function getUserList(Request $request){
        //Request
        $room_id = $request->json()->get("map_room_id");
        //Work
        $UserIDList = Room2User::where('id', $room_id)->get()->toArray();
        $UserIDList = array_column($UserIDList,"user_id");
        $UserList = User::whereIn('id', $UserIDList)->get()->toArray();
        $UserListData = array();
        foreach ($UserList as $value){
            $Temp["user_id"] = $value["id"];
            $Temp["nick"] = $value["name"];
            $UserListData[] = $Temp;
        }
        //回應
        return response()->json([
            'code' => 200,
            'members' => $UserListData,
        ]);
    }
    public function joinRoom(Request $request){
        //Request
        $room_id = $request->json()->get("map_room_id");
        $user_id_array = $request->json()->get("map_user_id");
        $membersNum = 0;
        if(Room2User::where('id', $room_id)->where('user_id',Auth::user()["id"])->count() == 0){
            return response()->json([
                'code' => 500,
                'msg' => "您不再房間內",
            ]);
        }
        foreach ($user_id_array as $value){
            if(Room2User::where('id', $room_id)->where('user_id',$value)->count() > 0) break;
            //新增
            $oRoom2User = new Room2User();
            $oRoom2User->id = $room_id;
            $oRoom2User->user_id = $value;
            $oRoom2User->save();
            $membersNum++;
        }
        //回應
        return response()->json([
            'code' => 200,
            'membersNum' => $membersNum,
            'room_id' => $room_id,
        ]);
    }
    public function kickRoom(Request $request){
        //Request
        $room_id = $request->json()->get("map_room_id");
        $user_id = $request->json()->get("map_user_id");
        //
        if(Room2User::where('id', $room_id)->where('user_id',Auth::user()["id"])->count() == 0){
            return response()->json([
                'code' => 500,
                'msg' => "您不再房間內",
            ]);
        }
        $is_Exist = Room2User::where('id',$room_id)->where('user_id',$user_id)->count();
        if($is_Exist)
            $rs = Room2User::where('id',$room_id)->where('user_id',$user_id)->delete();
        //回應
        if($rs){
            return response()->json([
                'code' => 200,
            ]);
        }else{
            return response()->json([
                'code' => 500,
                'msg' => 'Kick error!' ,
            ]);
        }

    }
}
