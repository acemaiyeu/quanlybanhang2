<?php

namespace App\Transformers;

use App\Models\Permission;
use Carbon\Carbon;
use League\Fractal\TransformerAbstract;

class PermissionTransformer extends TransformerAbstract
{
    public function transform(Permission $permission)
    {
        return [
            'id' => $permission->id,
            'code' => $permission->code,
            'title' => $permission->title,
            'details' => $permission->details ?? [],
            'created_at' => Carbon::parse($permission->created_at)->format('Y-m-d H:i:s'),
        ];
    }
}
