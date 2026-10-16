<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Brand extends Model
{
    use BelongsToCompany;

    protected $guarded = ['id', 'company_id'];

    public function teams(): HasMany { return $this->hasMany(Team::class); }
    public function users(): HasMany { return $this->hasMany(User::class); }
    public function bookings(): HasMany { return $this->hasMany(Booking::class); }

    public function director()
    {
        return $this->hasOne(User::class)->where('role', 'director');
    }

    public function initials(): string
    {
        return strtoupper(substr($this->code, 0, 2));
    }
}
