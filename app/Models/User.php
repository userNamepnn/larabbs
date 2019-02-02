<?php

namespace App\Models;

use Illuminate\Auth\MustVerifyEmail as MustVerifyEmailTrait;
use Illuminate\Contracts\Auth\MustVerifyEmail as MustVerifyEmailContract;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmailContract
{
    use MustVerifyEmailTrait, HasRoles;

    /**
     * 定制notify方法
     */
    use Notifiable {
        notify as protected laravelNotify;
    }

    public function notify($instance)
    {
        // 如果要通知的人是当前用户，就不必通知了！
        if ($this->id == \Auth::id()) {
            return;
        }

        // 只有数据库类型通知才需提醒，直接发送 Email 或者其他的都 Pass
        if (method_exists($instance, 'toDatabase')) {
            $this->increment('notification_count');
        }

        $this->laravelNotify($instance);
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'avatar', 'introduction'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * 用户-话题 一对多
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function topics()
    {
        // 使用$user->topics获取用户发布的话题
        return $this->hasMany(Topic::class);
    }

    /**
     * 用户-回复 一对多
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function replies()
    {
        return $this->hasMany(Reply::class);
    }

    /**
     * 清除未读消息
     */
    public function markAsRead()
    {
        $this->notification_count = 0;
        $this->save();
        $this->unreadNotifications->markAsRead();
    }

    /**
     * 用户密码字段修改器  set{属性的驼峰式命名}Attribute 非60长度字符串则为后台修改=》加密
     * @param $value
     */
    public function setPassWordAttribute($value)
    {
        if (60 !== strlen($value)) {
            $value = bcrypt($value);
        }
        $this->attributes['password'] = $value;
    }

    /**
     * 用户头像字段修改器 无http字符串则为后台上传=》拼接完整url
     * @param $path
     */
    public function setAvatarAttribute($path)
    {
        if (!starts_with($path, 'http')) {
            $path = config('app.url') . "/uploads/images/avatars/{$path}";
        }
        $this->attributes['avatar'] = $path;
    }
}
