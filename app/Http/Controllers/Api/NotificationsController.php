<?php

namespace App\Http\Controllers\Api;

use App\Transformers\NotificationsTransformer;

class NotificationsController extends Controller
{
    /**
     * 用户通知消息列表
     * @return \Dingo\Api\Http\Response
     */
    public function index()
    {
        $notifications = $this->user()->notifications()->paginate(20);

        return $this->response->paginator($notifications, new NotificationsTransformer());
    }

    /**
     * 用户未读通知数量
     * @return mixed
     */
    public function stats()
    {
        return $this->response->array([
            'unread_count' => $this->user()->notification_count,
        ]);
    }

    /**
     * 标记通知已读
     * @return \Dingo\Api\Http\Response
     */
    public function read()
    {
        $this->user()->markAsRead();

        return $this->response->noContent();
    }
}
