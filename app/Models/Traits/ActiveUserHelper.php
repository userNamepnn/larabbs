<?php
/**
 * Created by PhpStorm.
 * User: panninan
 * Date: 2019/2/3
 * Time: 16:46
 * 获取平台活跃用户
 */

namespace App\Models\Traits;

use App\Models\Reply;
use App\Models\Topic;
use Cache;
use Carbon\Carbon;
use DB;

trait ActiveUserHelper
{
    //活跃用户容器
    protected $users = [];

    //配置信息
    protected $topic_weight = 4; //话题权重
    protected $replay_weight = 1; //回复权重
    protected $pass_days = 7; //多少天内发布过内容
    protected $user_number = 6; //选取人数

    //缓存相关配置
    protected $cache_key = 'larabbs_active_users1';
    protected $cache_expire_in_minutes = 65;

    /**
     * 获取活跃用户
     * @return mixed
     */
    public function getActiveUsers()
    {
        //先从缓存中取,取不到则运行匿名函数,取出数据并缓存
        return Cache::remember($this->cache_key, $this->cache_expire_in_minutes, function () {
            return $this->calculateAndCacheActiveUsers();
        });
    }

    /**
     * 计算并缓存活跃用户
     */
    public function calculateAndCacheActiveUsers()
    {
        // 取得活跃用户列表
        $active_users = $this->calculateActiveUsers();

        // 并加以缓存
        $this->cacheActiveUsers($active_users);

        return $active_users;
    }

    /**
     * 计算活跃用户
     * @return \Illuminate\Support\Collection
     */
    private function calculateActiveUsers()
    {
        $this->calculateTopicScore();
        $this->calculateReplyScore();

        //数组按照得分排序
        $users = array_sort($this->users, function ($user) {
            return $user['score'];
        });

        $users = array_reverse($users, true);

        //截取需要的长度
        $users = array_slice($users, 0, $this->user_number, true);

        //新建一个空集合
        $active_users = collect();

        foreach ($users as $user_id => $user) {
            $user = $this->find($user_id);
            //是否有该用户
            if ($user) {
                $active_users->push($user);
            }
        }

        return $active_users;
    }

    /**
     * 计算话题发布得分情况
     */
    private function calculateTopicScore()
    {
        // 从话题数据表里取出限定时间范围（$pass_days）内，有发表过话题的用户
        // 并且同时取出用户此段时间内发布话题的数量
        $topic_users = Topic::query()
            ->select(DB::raw('user_id, count(*) as topic_count'))
            ->where('created_at', '>=', Carbon::now()->subDays($this->pass_days))
            ->groupBy('user_id')
            ->get();

        //根据回复数量计算得分
        foreach ($topic_users as $value) {
            $this->users[$value->user_id]['score'] = $value->topic_count * $this->topic_weight;
        }
    }

    /**
     * 计算回复得分情况
     */
    private function calculateReplyScore()
    {
        // 从回复数据表里取出限定时间范围（$pass_days）内，有发表过回复的用户
        // 并且同时取出用户此段时间内发布回复的数量
        $reply_users = Reply::query()
            ->select(DB::raw('user_id, count(*) as reply_count'))
            ->where('created_at', '>=', Carbon::now()->subDays($this->pass_days))
            ->groupBy('user_id')
            ->get();
        foreach ($reply_users as $value) {
            $reply_score = $value->reply_count * $this->replay_weight;
            if (isset($this->users[$value->user_id])) {
                $this->users[$value->user_id]['score'] += $reply_score;
            } else {
                $this->users[$value->user_id]['score'] = $reply_score;
            }
        }
    }

    /**
     * 缓存活跃用户
     * @param $active_users
     * @return mixed
     */
    private function cacheActiveUsers($active_users)
    {
        Cache::put($this->cache_key, $active_users, $this->cache_expire_in_minutes);
    }
}