<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'google_id',
        'role'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'custom_labels' => 'array',
        ];
    }

    public function classes()
    {
        // A student belongs to many classes through the class_students pivot table
        return $this->belongsToMany(SchoolClass::class, 'class_students', 'student_id', 'school_class_id');
    }

    public function subscriptions() {
        return $this->hasMany(UserSubscription::class)
                    ->join('subscription_plans', 'user_subscriptions.plan_id', '=', 'subscription_plans.id')
                    ->select('user_subscriptions.*', 'subscription_plans.name as plan_name')
                    ->orderBy('starts_at', 'asc');
    }
}
