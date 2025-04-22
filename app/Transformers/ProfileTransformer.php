<?php

namespace App\Transformers;

use App\Models\User;
use League\Fractal\TransformerAbstract;

class ProfileTransformer extends TransformerAbstract
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
            'email' => $user->email,
            'phone' => $user->phone,
            'city' => $user->city->name ?? '',
            'district' => $user->district->name ?? '',
            'wards' => $user->ward->name ?? '',
            'role' => $user->role->name,
        ];
    }
}
