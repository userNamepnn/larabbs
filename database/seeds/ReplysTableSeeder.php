<?php

use Illuminate\Database\Seeder;
use App\Models\Reply;

class ReplysTableSeeder extends Seeder
{
    public function run()
    {
        //所有用户 ID 数组，如：[1,2,3,4]
        $users = \App\Models\User::all()->pluck('id')->toArray();

        //所有话题 ID 数组，如：[1,2,3,4]
        $topics = \App\Models\Topic::all()->pluck('id')->toArray();

        //获取 Faker 实例
        $faker = app(Faker\Generator::class);

        $replys = factory(Reply::class)
            ->times(1000)
            ->make()
            ->each(function ($reply, $index)
            use ($users, $topics, $faker) {
                $reply->user_id = $faker->randomElement($users);
                $reply->topic_id = $faker->randomElement($topics);
            });

        Reply::insert($replys->toArray());
    }

}

