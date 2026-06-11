<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Roadmap;

class Turma extends Model
{
    protected $fillable = [
        'nome',
        'descricao',
        'docente_id'
    ];

    public function docente()
    {
        return $this->belongsTo(User::class, 'docente_id');
    }

    public function alunos()
    {
        return $this->belongsToMany(User::class, 'turma_user')->withTimestamps();
    }

    public function roadmaps()
    {
        return $this->hasMany(Roadmap::class);
    }
}
