<?php
/**
 * Created by PhpStorm.
 * User: panninan
 * Date: 2019/2/3
 * Time: 21:48
 */

namespace App\Models\Traits;

use Carbon\Carbon;
use Redis;

trait LastActivedAtHelper
{
    //缓存相关
    protected $hash_prefix = 'users_last_actived_at';
    protected $field_prefix = 'user_';

    /**
     * 记录用户最后活跃时间至redis
     */
    public function recordLastActivedAt()
    {
        //redis 哈希表命名
        $hash = $this->getHashFromDateString(Carbon::now()->toDateString());

        //字段名
        $field = $this->getHashField();

        //获取当前时间
        $now = Carbon::now()->toDateTimeString();

        //数据写入redis
        Redis::hSet($hash, $field, $now);
    }

    /**
     * 同步redis数据至database
     */
    public function syncUserActivedAt()
    {
        //redis hash表名称
        $hash = $this->getHashFromDateString(Carbon::yesterday()->toDateString());

        //获取redis数据
        $data = Redis::hGetAll($hash);

        //遍历同步入库
        foreach ($data as $user_id => $actived_at) {
            $user_id = str_replace($this->field_prefix, '', $user_id);
            if ($user = $this->find($user_id)) {
                $user->last_actived_at = $actived_at;
                $user->save();
            }
        }

        //删除昨天数据
        Redis::del($hash);
    }

    /**
     * 获取用户最后活跃时间
     * @param $value
     * @return Carbon
     */
    public function getLastActivedAtAttribute($value)
    {
        //redis hash表名称
        $hash = $this->getHashFromDateString(Carbon::now()->toDateString());

        //字段名
        $field = $this->getHashField();

        $datetime = Redis::hGet($hash, $field) ?: $value;

        if ($datetime) {
            return new Carbon($datetime);
        } else {
            return $this->created_at;
        }
    }

    /**
     * 根据指定日期获取hash表名
     * @param $date
     * @return string
     */
    private function getHashFromDateString($date)
    {
        // Redis 哈希表的命名，如：larabbs_last_actived_at_2017-10-21
        return $this->hash_prefix . $date;
    }

    /**
     * 获取hash表字段名
     * @return string
     */
    private function getHashField()
    {
        // 字段名称，如：user_1
        return $this->field_prefix . $this->id;
    }
}