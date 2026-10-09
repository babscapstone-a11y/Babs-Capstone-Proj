<?php

namespace App\Models\Concerns;

use Illuminate\Support\Str;

/**
 * Gives a model a random, unguessable public identifier (a ULID) that is used in URLs
 * instead of the sequential database id — e.g. /customers/01k7c9q2... rather than /customers/5.
 * The numeric id is still the primary key, so relationships and logins are unaffected.
 */
trait HasPublicId
{
    protected static function bootHasPublicId(): void
    {
        static::creating(function ($model) {
            if (empty($model->public_id)) {
                $model->public_id = static::newPublicId();
            }
        });
    }

    public static function newPublicId(): string
    {
        return strtolower((string) Str::ulid());
    }

    /** Route model binding (and route() URL generation) use the public id */
    public function getRouteKeyName(): string
    {
        return 'public_id';
    }
}
