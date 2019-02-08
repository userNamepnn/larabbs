<?php
/**
 * Created by PhpStorm.
 * User: panninan
 * Date: 2019/2/8
 * Time: 17:08
 */

namespace App\Transformers;

use League\Fractal\TransformerAbstract;
use Spatie\Permission\Models\Permission;

class PermissionsTransformer extends TransformerAbstract
{
    protected $availableIncludes = ['user', 'topic'];

    public function transform(Permission $permission)
    {
        return [
            'id' => $permission->id,
            'name' => $permission->name,
        ];
    }
}