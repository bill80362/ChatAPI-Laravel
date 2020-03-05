<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Msg;
use App\Room;
use App\Room2User;
use App\MsgStream;

use Illuminate\Support\Facades\Redis;

class MsgController extends Controller
{
    public function sendMsg(Request $request)
    {
        $room_id = $request->json()->get("msg_room_id");
        $msg_message = $request->json()->get("msg_message");
        if(Room2User::where('id', $room_id)->where('user_id',Auth::user()["id"])->count() == 0){
            return response()->json([
                'code' => 500,
                'msg' => "您不再房間內",
            ]);
        }
        //儲存
        $oMsg = new Msg();
        $oMsg->user_id = Auth::user()["id"];
        $oMsg->MsgType = "C";
        $oMsg->room_id = $room_id;
        $oMsg->MsgContent = $msg_message;
        $rs = $oMsg->save();
        //回應
        if($rs){
            //Redis
            $oMsgStream = new MsgStream(Auth::user()["id"]);
            if($oMsgStream->isUserStream()){
                $Data = array(
                    'msg_add_time' =>$oMsg->created_at,
                    'msg_id' => $oMsg->id,
                    'msg_message' => $oMsg->MsgContent,
                    'msg_room_id' => $oMsg->room_id,
                    'msg_system' => $oMsg->MsgType,
                    'msg_user_id' => $oMsg->user_id,
                    'room_name' => Room::find($oMsg->room_id)["RoomName"],
                    'user_id' => $oMsg->user_id,
                );
                $oMsgStream->pushData($Data);
            }


            return response()->json([
                'code' => 200,
                'room_id' => $room_id,
                'msg_message' => $msg_message,
                'msg_id' => $oMsg->id,
            ]);
        }else{
            return response()->json([
                'code' => 500,
                'msg' => 'send msg error' ,
            ]);
        }
    }
    public function readMsg(Request $request)
    {
        $request_array = $request->toArray();

        foreach ($request_array as $value){
            $room_id = $value["msg_room_id"];
            $msg_id = $value["msg_id"];
            if(Room2User::where('id', $room_id)->where('user_id',Auth::user()["id"])->count() == 0){
                break;
            }
            $MsgData[] = Msg::where('room_id',$room_id)->where('id','>',$msg_id)->get()->toArray();
        }
        return response()->json([
            'code' => 200,
            'data' => $MsgData,
        ]);
    }

    public function stream(){
        ignore_user_abort(true);
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('Connection: keep-alive');

        $UserID = Auth::user()["id"];
        $UserToken = Auth::user()["token"];
        $UserID = 2;
        $UserToken = "AAAAA";
        $oMsgStream = new MsgStream($UserID,true,$UserToken);
        //傳連線成功訊息
        echo "id:".$UserID." \n";
        echo "event: message\n";
        echo "data: " . json_encode(array("Link"=>"connected"));
        echo "\n\n";
        // 讓迴圈無限執行
        for($i=0;$i<50;$i++) {
            $Data  = $oMsgStream->getDataNoLock();
            if($Data){
                //將資料內容編碼json傳送
                echo "id:".$UserID." \n";
                echo "event: message\n";
                echo "data: " . json_encode($Data);
                echo "\n\n";
            }else{
                //持續連線中
                echo "id:".$UserID." \n";
                echo "event: link\n";//改成非message讓client辨識為無用訊息
                echo "data: " . json_encode(array("Link"=>"Linking..."));
                echo "\n\n";
            }

            //輸出暫存
            ob_flush();
            flush();
            //偵測使用者關閉連線(前面一定要有輸出才能偵測)
            if(connection_aborted()){
                exit();
            }
            // 控制睡眠多久再執行（秒）
            sleep(1);
        }
    }
    public function redis(){
//        app()->make('Hello');
//        dd(app());
        dd(app()->make('Hello'));
        return "Hello";
    }

}
