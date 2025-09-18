<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     * Limitado apenas aos campos seguros para mass assignment
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'bio',
        // Removido 'avatar' do fillable por segurança - deve ser atualizado separadamente
    ];

    /**
     * The attributes that should be hidden for serialization.
     * Campos sensíveis que não devem aparecer em responses
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be guarded against mass assignment.
     * Campos críticos que nunca devem ser mass assigned
     */
    protected $guarded = [
        'id',
        'email_verified_at',
        'avatar', // Avatar deve ser atualizado através de processo específico
        'created_at',
        'updated_at',
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
        ];
    }

    public function settings(): HasOne
    {
        return $this->hasOne(UserSettings::class);
    }

    public function getOrCreateSettings(): UserSettings
    {
        if (!$this->settings) {
            $this->settings()->create(UserSettings::getDefaultSettings());
            $this->load('settings');
        }
        
        return $this->settings;
    }

    public function getAvatarUrlAttribute(): ?string
    {
        if ($this->avatar) {
            return route('avatar.show', ['filename' => $this->avatar]);
        }
        
        return "https://ui-avatars.com/api/?name=" . urlencode($this->name) . "&color=7F9CF5&background=EBF4FF";
    }
}
