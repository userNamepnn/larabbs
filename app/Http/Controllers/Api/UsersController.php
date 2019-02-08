<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\UserRequest;
use App\Models\Image;
use App\Models\User;
use App\Transformers\UserTransformer;

class UsersController extends Controller
{
    /**
     * 用户注册接口
     * @param UserRequest $request
     * @return \Dingo\Api\Http\Response|void
     */
    public function store(UserRequest $request)
    {
        $verifyData = \Cache::get($request->verification_key);
        if (!$verifyData) {
            return $this->response->error('验证码已失效', 422);
        }
        if (!hash_equals($verifyData['code'], $request->verification_code)) {
            return $this->response->error('验证码错误', 401);
        }

        $user = User::insert([
            'name' => $request->name,
            'phone' => $verifyData['phone'],
            'password' => bcrypt($request->input('password')),
        ]);

        //清除验证码缓存
        \Cache::forget($request->varification_key);

        return $this->response->item($user, new UserTransformer())
            ->meta([
                'access_token' => \Auth::guard('api')->fromUser($user),
                'token_type' => 'Bearer',
                'expires_in' => \Auth::guard('api')->factory()->getTTL() * env('JWT_TTL')
            ])->setStatusCode(201);
    }

    /**
     * 登录用户信息
     * @return mixed
     */
    public function me()
    {
        return $this->response->item($this->user(), new UserTransformer());
    }

    /**
     * 更新用户信息
     * @param UserRequest $request
     * @return \Dingo\Api\Http\Response
     */
    public function update(UserRequest $request)
    {
        $user = $this->user();

        $attributes = $request->only(['name', 'email', 'introduction', 'registration_id']);

        if ($request->avatar_image_id) {
            $image = Image::find($request->avatar_image_id);
            $attributes['avatar'] = $image->path;
        }

        $user->update($attributes);

        return $this->response->item($user, new UserTransformer());
    }

    /**
     * 活跃用户列表
     * @param User $user
     * @return \Dingo\Api\Http\Response
     */
    public function activedIndex(User $user)
    {
        return $this->response->collection($user->getActiveUsers(), new UserTransformer);
    }
}
