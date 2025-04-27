<?php

namespace App\Transformers;

use App\Models\User;
use Carbon\Carbon;
use League\Fractal\TransformerAbstract;

class AccountTransformer extends TransformerAbstract
{
    /**
     * List of resources to automatically include
     *
     * @var array
     */
    protected array $defaultIncludes = [
        //
    ];

    /**
     * List of resources possible to include
     *
     * @var array
     */
    protected array $availableIncludes = [
        //
    ];

    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(User $user)
    {
        return [
            'id' => $user->id,
            'fullname' => $user->fullname ?? null,
            'phone' => $user->phone ?? null,
            'email' => $user->email ?? '',
            'city' => $user->city ?? null,
            'district' => $user->district ?? null,
            'ward' => $user->ward ?? null,
            'role' => $user->role,
            'created_at' => Carbon::parse($user->created_at)->format('d/m/Y H:i:s'),
        ];
    }
}
