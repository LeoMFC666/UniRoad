<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Turma;

class Roadmap extends Model
{
    protected $fillable = [
        'turma_id',
        'titulo',
        'descricao',
        'conteudo_json',
    ];

    /**
     * Retorna estrutura sempre normalizada do roadmap (PHP-first)
     */
    public function getGraph(): array
    {
        $data = $this->conteudo_json;

        if (is_string($data)) {
            $decoded = json_decode($data, true);
            $data = json_last_error() === JSON_ERROR_NONE ? $decoded : [];
        }

        if (!is_array($data)) {
            $data = [];
        }

        return [
            'nodes' => $data['nodes'] ?? [],
            'connections' => $data['connections'] ?? [],
        ];
    }

    /**
     * Define o estado completo do roadmap
     */
    public function setGraph(array $graph): void
    {
        $this->conteudo_json = [
            'nodes' => $graph['nodes'] ?? [],
            'connections' => $graph['connections'] ?? [],
        ];
    }

    /**
     * Adiciona um node no roadmap (PHP-first logic)
     */
    public function addNode(array $node): void
    {
        $graph = $this->getGraph();
        $graph['nodes'][] = $node;
        $this->setGraph($graph);
    }

    /**
     * Remove node por ID
     */
    public function removeNode(string $nodeId): void
    {
        $graph = $this->getGraph();

        $graph['nodes'] = array_filter(
            $graph['nodes'],
            fn ($n) => ($n['id'] ?? null) !== $nodeId
        );

        $this->setGraph($graph);
    }

    /**
     * Atualiza node
     */
    public function updateNode(string $nodeId, array $data): void
    {
        $graph = $this->getGraph();

        foreach ($graph['nodes'] as &$node) {
            if (($node['id'] ?? null) === $nodeId) {
                $node = array_merge($node, $data);
            }
        }

        $this->setGraph($graph);
    }

    public function turma()
    {
        return $this->belongsTo(Turma::class);
    }
}