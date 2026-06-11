<?php

namespace App\Models;

use App\Models\Turma;
use App\Notifications\VerifyEmailNotification;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Log;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function turmas()
    {
        return $this->belongsToMany(Turma::class, 'turma_user')->withTimestamps();
    }

    public function turmasDocente()
    {
        return $this->hasMany(Turma::class, 'docente_id');
    }

    public function sendEmailVerificationNotification(): void
    {
        if (! config('mail.email_verification_enabled')) {
            Log::info('E-mail de verificação pulado porque o envio externo está desativado.', [
                'user_id' => $this->id,
            ]);

            return;
        }

        $this->notify(new VerifyEmailNotification);
    }
}
