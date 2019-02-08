<?php
/**
 * Created by PhpStorm.
 * User: panninan
 * Date: 2019/2/8
 * Time: 21:36
 */

namespace App\Transformers;

use App\Models\Link;
use League\Fractal\TransformerAbstract;

class LinksTransformer extends TransformerAbstract
{
    public function transform(Link $link)
    {
        return [
            'id' => $link->id,
            'title' => $link->title,
            'link' => $link->link,
        ];
    }
}