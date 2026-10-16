<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    public const ROLES = [
        'admin' => 'Admin',
        'director' => 'Director',
        'team_lead' => 'Team lead',
        'agent' => 'Agent',
        'accounts' => 'Accounts',
    ];

    /** Role → permission matrix shown on the Team & roles page. */
    public const PERMISSIONS = [
        'All brands & bookings' => ['admin', 'accounts'],
        'Own brand bookings' => ['admin', 'director', 'accounts'],
        'Team bookings' => ['admin', 'director', 'team_lead'],
        'Own bookings' => ['admin', 'director', 'team_lead', 'agent'],
        'Create / edit bookings' => ['admin', 'director', 'team_lead', 'agent'],
        'View profit / loss' => ['admin', 'director', 'team_lead', 'agent', 'accounts'],
        'Record payments' => ['admin', 'director', 'accounts'],
        'Manage users' => ['admin', 'director'],
    ];

    protected $fillable = ['name', 'email', 'password', 'role', 'status', 'brand_id', 'team_id', 'company_id'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return ['email_verified_at' => 'datetime', 'password' => 'hashed'];
    }

    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
    public function brand(): BelongsTo { return $this->belongsTo(Brand::class); }
    public function team(): BelongsTo { return $this->belongsTo(Team::class); }

    public function roleLabel(): string { return self::ROLES[$this->role] ?? ucfirst($this->role); }

    public function hasRole(string ...$roles): bool { return in_array($this->role, $roles, true); }

    public function canCreateBookings(): bool { return $this->hasRole('admin', 'director', 'team_lead', 'agent'); }
    public function canRecordPayments(): bool { return $this->hasRole('admin', 'director', 'accounts'); }
    public function canManageUsers(): bool { return $this->hasRole('admin', 'director'); }
    public function canManageCompany(): bool { return $this->hasRole('admin'); }

    public function canViewBooking(Booking $b): bool
    {
        if ($b->company_id !== $this->company_id) return false;
        return match ($this->role) {
            'admin', 'accounts' => true,
            'director' => $b->brand_id === $this->brand_id,
            'team_lead' => $b->agent_id === $this->id || ($this->team_id && $b->team_id === $this->team_id),
            default => $b->agent_id === $this->id,
        };
    }

    public function canEditBooking(Booking $b): bool
    {
        return $this->canCreateBookings() && $this->canViewBooking($b);
    }

    public function initials(): string
    {
        $p = preg_split('/\s+/', trim($this->name));
        return strtoupper(substr($p[0], 0, 1).substr(end($p), 0, 1));
    }

    public function tone(): string
    {
        return ['admin' => 'brand', 'director' => 'success', 'team_lead' => 'warning', 'agent' => 'info', 'accounts' => 'brand'][$this->role] ?? 'info';
    }
}
