<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Submissao extends Model
{
    protected $table = 'submissoes';

    protected $fillable = [
        'atividade_id',
        'user_id',
        'conteudo',
        'arquivo',
        'arquivos',
        'nota',
    ];

    protected $casts = [
        'arquivos' => 'array',
    ];

    public function atividade()
    {
        return $this->belongsTo(Atividade::class);
    }

    public function aluno()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
