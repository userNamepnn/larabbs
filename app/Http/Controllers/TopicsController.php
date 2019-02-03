<?php

namespace App\Http\Controllers;

use App\Handlers\ImageUploadHandler;
use App\Models\Category;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\TopicRequest;
use Illuminate\Support\Facades\Auth;

class TopicsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth', ['except' => ['index', 'show']]);
    }

    public function index(Request $request, Topic $topic, User $user)
    {
        $active_users = $user->getActiveUsers();

        $topics = Topic::withOrder($request->order)->paginate(6);
        return view('topics.index', compact('topics', 'active_users'));
    }

    public function show(Topic $topic, Request $request)
    {
        // URL 矫正
        if (!empty($topic->slug) && $topic->slug != $request->slug) {
            return redirect($topic->link(), 301);
        }
        return view('topics.show', compact('topic'));
    }

    public function create(Topic $topic)
    {
        $categories = Category::all();
        return view('topics.create_and_edit', compact('topic', 'categories'));
    }

    public function store(TopicRequest $request, Topic $topic)
    {
        $topic->fill($request->all());
        $topic->user_id = Auth::id();
        $topic->save();
        return redirect()->to($topic->link())->with('success', '成功创建话题！');
    }

    public function edit(Topic $topic)
    {
        $this->authorize('update', $topic);
        $categories = Category::all();
        return view('topics.create_and_edit', compact('topic', 'categories'));
    }

    public function update(TopicRequest $request, Topic $topic)
    {
        $this->authorize('update', $topic);
        $topic->update($request->all());
        return redirect()->to($topic->link())->with('success', '更新成功');
    }

    public function destroy(Topic $topic)
    {
        $this->authorize('destroy', $topic);
        $topic->delete();
        return redirect()->route('topics.index')->with('success', '成功删除');
    }

    /**
     * 话题创建，编辑上传图片
     * @param Request $request
     * @param ImageUploadHandler $uploadHandler
     * @return array
     */
    public function uploadImage(Request $request, ImageUploadHandler $uploadHandler)
    {
        $response = [
            'success' => false,
            'msg' => '上传失败!',
            'file_path' => ''
        ];
        if ($request->upload_file) {
            $result = $uploadHandler->save($request->upload_file, 'topics', \Auth::id(), 1024);
            if ($result) {
                $response = [
                    'success' => true,
                    'msg' => '上传成功!',
                    'file_path' => $result['path'],
                ];
            }
        }

        return $response;
    }
}
