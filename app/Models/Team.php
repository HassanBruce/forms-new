<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Team extends Model
{
    use BelongsToCompany;

    protected $guarded = ['id', 'company_id'];

    public function brand(): BelongsTo { return $this->belongsTo(Brand::class); }
    public function lead(): BelongsTo { return $this->belongsTo(User::class, 'lead_id'); }
    public function members(): HasMany { return $this->hasMany(User::class); }
}
