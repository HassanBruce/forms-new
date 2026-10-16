<?php

namespace App\Models\Concerns;

use App\Models\Company;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

/**
 * Multi-tenant isolation: every query is limited to the signed-in user's company,
 * and new rows are stamped with it automatically.
 */
trait BelongsToCompany
{
    public static function bootBelongsToCompany(): void
    {
        static::addGlobalScope('company', function (Builder $q) {
            if (Auth::hasUser() && Auth::user()->company_id) {
                $q->where($q->getModel()->getTable().'.company_id', Auth::user()->company_id);
            }
        });

        static::creating(function ($model) {
            if (! $model->company_id && Auth::hasUser()) {
                $model->company_id = Auth::user()->company_id;
            }
        });
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
