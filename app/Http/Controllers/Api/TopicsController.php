<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\TopicRequest;
use App\Models\Topic;
use App\Transformers\TopicTransformer;

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
}
