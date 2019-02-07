<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\TopicRequest;
use App\Models\Topic;
use App\Models\User;
use App\Transformers\TopicTransformer;
use Illuminate\Http\Request;

class TopicsController extends Controller
{
    /**
     * 发布话题
     * @param TopicRequest $request
     * @param Topic $topic
     * @param TopicTransformer $transformer
     * @return \Dingo\Api\Http\Response
     */
    public function store(TopicRequest $request, Topic $topic, TopicTransformer $transformer)
    {
        $topic->fill($request->all());
        $topic->user_id = $this->user()->id;
        $topic->save();

        return $this->response->item($topic, $transformer)->setStatusCode(201);
    }

    /**
     * 修改话题
     * @param TopicRequest $request
     * @param Topic $topic
     * @param TopicTransformer $transformer
     * @return \Dingo\Api\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function update(TopicRequest $request, Topic $topic, TopicTransformer $transformer)
    {
        $this->authorize('update', $topic);

        $topic->update($request->all());

        return $this->response->item($topic, $transformer);
    }

    /**
     * 删除话题
     * @param Topic $topic
     * @return \Dingo\Api\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function destroy(Topic $topic)
    {
        $this->authorize('destroy', $topic);

        $topic->delete();

        return $this->response->noContent();
    }

    public function index(Request $request, Topic $topic, TopicTransformer $transformer)
    {
        $query = $topic->query();

        if ($category_id = $request->category_id) {
            $query->where('category_id', $category_id);
        }

        switch ($request->order) {
            case 'recent':
                $query->recent();
                break;

            default:
                $query->recentReplied();
                break;
        }

        $topics = $query->paginate(20);

        return $this->response->paginator($topics, $transformer);
    }

    /**
     * 指定用户话题列表
     * @param User $user
     * @param Request $request
     * @return \Dingo\Api\Http\Response
     */
    public function userIndex(User $user, Request $request)
    {
        $topics = $user->topics()->recent()->paginate(20);

        return $this->response->paginator($topics, new TopicTransformer());
    }

    /**
     * 获取话题详情
     * @param Topic $topic
     * @return \Dingo\Api\Http\Response
     */
    public function show(Topic $topic)
    {
        return $this->response->item($topic, new TopicTransformer());
    }
}
