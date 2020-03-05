<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Redis;

class MsgStream extends Model
{
    static public $PreKey = "Chat_User_";//KEY的前置
    public $Key;
    public $UserID;
    public $isStream = false;
    public $redis;
    public $Token;

    public function __construct($UserID,$isStream=false,$_Token="")
    {
        $this->UserID = $UserID;
        $this->Key = $this->PreKey.$UserID;//儲存KEY使用的前置
        $this->isStream = $isStream;//是否為串流使用
        $this->Token = $_Token;//紀錄Token
        //Redis連線
//        $this->redis = new Redis();
//        $this->redis->connect(REDIS_HOST, REDIS_PORT,60);
//        $this->redis->select(REDIS_DB_INDEX);

        //設定用戶連線中
        if($this->isStream)
            Redis::set($this->Key,$_Token) ;//上線標記
    }
    //SSE用戶關閉視窗會觸發
    public function __destruct()
    {
        //目前用戶的redis的token是否一致(不同代表同用戶重複登入)
        $redisToken = Redis::get($this->Key);
        //&& $this->Token==$redisToken
        //用戶離線
        if($this->isStream && $this->Token==$redisToken ){
            Redis::del($this->Key); //bill離線
            Redis::del("Msg_".$this->Key);//清空對列
        }
        //測試用-離線會記錄在DB
//        $oTestSSE = new TestSSE();
//        $oTestSSE->create(array("title"=>"用戶:，離線","content"=>MEMCACHE_TEST));
    }
    //確認該用戶是否為連線中
    public function isUserStream(){
        //bill是否在現在
        if( Redis::command('exists', [$this->Key]) )
            return true;
        else
            return false;
    }
    //拿資料不上鎖
    public function getDataNoLock(){
        $task = Redis::rpop("Msg_".$this->Key);
        $task = json_decode($task, true);
        return $task;
    }
    //新增資料
    public function pushData($_Data){
        Redis::lPush("Msg_".$this->Key, json_encode($_Data));
    }
}
