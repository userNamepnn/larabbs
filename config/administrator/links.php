<?php
/**
 * Created by PhpStorm.
 * User: panninan
 * Date: 2019/2/3
 * Time: 20:57
 */

use App\Models\Link;

return [
    'title' => '资源推荐',
    'single' => '资源推荐',

    'model' => Link::class,

    //访问权限
    'permission' => function () {
        //站长获权
        return \Auth::user()->hasRole('Founder');
    },

    'columns' => [
        'id' => [
            'title' => 'ID',
        ],
        'title' => [
            'title' => '资源名称',
            'sortable' => false,
        ],
        'link' => [
            'title' => '资源链接',
            'sortable' => false,
        ],
        'operation' => [
            'title' => '管理',
            'sortable' => false,
        ],
    ],
    'edit_fields' => [
        'title' => [
            'title' => '名称',
        ],
        'link' => [
            'title' => '链接',
        ],
    ],
    'filters' => [
        'id' => [
            'title' => '标签ID',
        ],
        'title' => [
            'title' => '名称',
        ],
    ],
];