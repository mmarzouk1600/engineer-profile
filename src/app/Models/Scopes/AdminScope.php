<?php

namespace App\Models\Scopes;
use App\Enums\UserGroupType;
use App\Models\Admin;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class AdminScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {
        $builder->whereIn('type', [
            UserGroupType::admin, UserGroupType::superadmin, UserGroupType::supervisor, UserGroupType::coordinator, UserGroupType::student, UserGroupType::GRADUATE,
        ]);
    }
}
