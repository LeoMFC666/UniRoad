<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Atividade extends Model
{
    protected $fillable = [
        'roadmap_id',
        'roadmap_node_id',
        'titulo',
        'descricao',
        'anexos',
        'data_entrega',
    ];

    protected $casts = [
        'anexos' => 'array',
        'data_entrega' => 'date',
    ];

    public function roadmap()
    {
        return $this->belongsTo(Roadmap::class);
    }

    public function submissoes()
    {
        return $this->hasMany(Submissao::class);
    }
}
