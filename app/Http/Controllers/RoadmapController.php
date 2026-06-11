<?php

namespace App\Http\Controllers;

use App\Models\Atividade;
use App\Models\Roadmap;
use App\Models\Submissao;
use App\Models\Turma;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class RoadmapController extends Controller
{
    private const MAX_ROADMAP_JSON_BYTES = 12_000_000;
    private const MAX_ROADMAP_NODES = 300;
    private const MAX_ROADMAP_CONNECTIONS = 800;
    private const MAX_ROADMAP_DEPTH = 10;
    private const MAX_ROADMAP_ARRAY_ITEMS = 1200;
    private const MAX_ROADMAP_STRING_BYTES = 4_000_000;

    public function index($turmaId)
    {
        $turma = Turma::with('alunos')->findOrFail($turmaId);

        $this->authorizeAccess($turma);

        $roadmaps = $turma->roadmaps()->get();

        return view('roadmaps.index', compact('turma', 'roadmaps'));
    }

    public function show($turmaId, $roadmapId)
    {
        $turma = Turma::with('alunos')->findOrFail($turmaId);
        $roadmap = Roadmap::where('turma_id', $turmaId)->findOrFail($roadmapId);

        $this->authorizeAccess($turma);

        $activityContext = $this->activityContextForViewer($roadmap, $turma);
        $activitySubmissions = $activityContext['studentSubmissions'];

        return view('roadmaps.show', compact('turma', 'roadmap', 'activitySubmissions', 'activityContext'));
    }

    public function create($turmaId)
    {
        $turma = Turma::findOrFail($turmaId);

        $this->authorizeProfessorEditor($turma);

        return view('roadmaps.create', compact('turma'));
    }

    public function store(Request $request, $turmaId)
    {
        $turma = Turma::findOrFail($turmaId);

        $this->authorizeProfessorEditor($turma);

        $validated = $request->validate([
            'titulo' => 'required|string|max:255',
            'descricao' => 'nullable|string|max:2000',
            'conteudo_json' => ['nullable', $this->roadmapPayloadRule()],
        ]);

        $validated['conteudo_json'] = $this->normalize($validated['conteudo_json']);

        $roadmap = $turma->roadmaps()->create($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'id' => $roadmap->id,
                'showUrl' => route('roadmaps.show', [$turmaId, $roadmap->id]),
                'updateUrl' => route('roadmaps.update', [$turmaId, $roadmap->id]),
                'saveUrl' => route('roadmaps.save', [$turmaId, $roadmap->id]),
                'data' => $roadmap->conteudo_json,
            ]);
        }

        return redirect()->route('roadmaps.index', $turmaId)
            ->with('success', 'Roadmap criado com sucesso!');
    }

    public function import(Request $request, $turmaId)
    {
        $turma = Turma::findOrFail($turmaId);

        $this->authorizeProfessorEditor($turma);

        $validated = $request->validate([
            'roadmap_file' => 'required|file|max:12288',
        ], [
            'roadmap_file.required' => 'Selecione um arquivo JSON para importar.',
            'roadmap_file.file' => 'O arquivo enviado não pôde ser lido.',
            'roadmap_file.max' => 'O arquivo JSON excede o tamanho permitido.',
        ]);

        $file = $validated['roadmap_file'];

        if (strtolower($file->getClientOriginalExtension()) !== 'json') {
            throw ValidationException::withMessages([
                'roadmap_file' => 'Envie apenas arquivos JSON.',
            ]);
        }

        $contents = file_get_contents($file->getRealPath());

        if (! is_string($contents) || trim($contents) === '') {
            throw ValidationException::withMessages([
                'roadmap_file' => 'Não foi possível ler o arquivo. Verifique se ele é um JSON exportado pela UniRoad.',
            ]);
        }

        try {
            if (strlen($contents) > self::MAX_ROADMAP_JSON_BYTES) {
                throw ValidationException::withMessages(['roadmap_file' => 'O roadmap excede o tamanho permitido.']);
            }

            $payload = json_decode($contents, true, flags: JSON_THROW_ON_ERROR);
            $graph = $this->extractImportedGraph($payload);

            $title = $this->extractImportedTitle($payload, $file->getClientOriginalName());
            $description = $this->extractImportedDescription($payload);

            $roadmap = $turma->roadmaps()->create([
                'titulo' => $title,
                'descricao' => $description,
                'conteudo_json' => $this->normalize($graph),
            ]);
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            throw ValidationException::withMessages([
                'roadmap_file' => 'Não foi possível ler o arquivo. Verifique se ele é um JSON exportado pela UniRoad.',
            ]);
        }

        return redirect()->route('roadmaps.index', $turmaId)
            ->with('success', "Roadmap \"{$roadmap->titulo}\" importado com sucesso!");
    }

    public function edit($turmaId, $roadmapId)
    {
        $turma = Turma::findOrFail($turmaId);
        $roadmap = Roadmap::where('turma_id', $turmaId)->findOrFail($roadmapId);

        $this->authorizeProfessorEditor($turma);

        return view('roadmaps.edit', compact('turma', 'roadmap'));
    }

    public function update(Request $request, $turmaId, $roadmapId)
    {
        $turma = Turma::findOrFail($turmaId);
        $roadmap = Roadmap::where('turma_id', $turmaId)->findOrFail($roadmapId);

        $this->authorizeProfessorEditor($turma);

        $validated = $request->validate([
            'titulo' => 'required|string|max:255',
            'descricao' => 'nullable|string|max:2000',
            'conteudo_json' => ['nullable', $this->roadmapPayloadRule()],
        ]);

        $validated['conteudo_json'] = $this->normalize($validated['conteudo_json']);

        $roadmap->update($validated);

        return redirect()->route('roadmaps.index', $turmaId)
            ->with('success', 'Roadmap atualizado com sucesso!');
    }

    public function destroy($turmaId, $roadmapId)
    {
        $turma = Turma::findOrFail($turmaId);
        $roadmap = Roadmap::where('turma_id', $turmaId)->findOrFail($roadmapId);

        $this->authorizeRoadmapDelete($turma);

        $roadmap->delete();

        return redirect()->route('roadmaps.index', $turmaId)
            ->with('success', 'Roadmap deletado com sucesso!');
    }

    public function save(Request $request, $turmaId, $roadmapId = null)
    {
        $turma = Turma::findOrFail($turmaId);
        $roadmap = Roadmap::where('turma_id', $turmaId)->findOrFail($roadmapId);

        $this->authorizeProfessorEditor($turma);

        $validated = $request->validate([
            'data' => ['required', 'array', $this->roadmapPayloadRule()],
            'titulo' => 'nullable|string|max:255',
            'title' => 'nullable|string|max:255',
            'descricao' => 'nullable|string|max:2000',
            'description' => 'nullable|string|max:2000',
        ]);

        $titulo = $validated['titulo'] ?? $validated['title'] ?? null;
        $descricao = $validated['descricao'] ?? $validated['description'] ?? null;

        if (is_string($titulo) && trim($titulo) !== '') {
            $roadmap->titulo = trim($titulo);
        }

        if (is_string($descricao)) {
            $roadmap->descricao = $descricao;
        }

        $roadmap->conteudo_json = $this->normalize($validated['data']);
        $roadmap->save();

        return response()->json([
            'success' => true,
            'titulo' => $roadmap->titulo,
            'data' => $roadmap->conteudo_json,
        ]);
    }

    public function addNode(Request $request, $turmaId, $roadmapId)
    {
        [$turma, $roadmap] = $this->editableRoadmap($turmaId, $roadmapId);

        $validated = $request->validate([
            'node' => ['required', 'array', $this->roadmapPayloadRule()],
        ]);

        $node = $this->sanitizeRoadmapValue($validated['node']);
        $node['id'] = isset($node['id']) && is_string($node['id']) && $node['id'] !== ''
            ? Str::limit($node['id'], 120, '')
            : (string) Str::uuid();

        $graph = $this->decodeGraph($roadmap->conteudo_json);
        $graph['nodes'] = array_values($graph['nodes'] ?? []);
        $graph['nodes'][] = $node;

        $this->assertRoadmapPayload($graph);

        $roadmap->conteudo_json = $this->normalize($graph);
        $roadmap->save();

        return response()->json([
            'success' => true,
            'node' => $node,
            'data' => $roadmap->conteudo_json,
        ]);
    }

    public function updateNode(Request $request, $turmaId, $roadmapId, $nodeId)
    {
        [$turma, $roadmap] = $this->editableRoadmap($turmaId, $roadmapId);

        $validated = $request->validate([
            'node' => ['required', 'array', $this->roadmapPayloadRule()],
        ]);

        $graph = $this->decodeGraph($roadmap->conteudo_json);
        $updated = false;

        foreach (($graph['nodes'] ?? []) as &$node) {
            if (($node['id'] ?? null) === $nodeId) {
                $node = array_merge($node, $this->sanitizeRoadmapValue($validated['node']), ['id' => $nodeId]);
                $updated = true;
                break;
            }
        }
        unset($node);

        abort_unless($updated, 404);

        $this->assertRoadmapPayload($graph);

        $roadmap->conteudo_json = $this->normalize($graph);
        $roadmap->save();

        return response()->json([
            'success' => true,
            'data' => $roadmap->conteudo_json,
        ]);
    }

    public function deleteNode($turmaId, $roadmapId, $nodeId)
    {
        [$turma, $roadmap] = $this->editableRoadmap($turmaId, $roadmapId);

        $graph = $this->decodeGraph($roadmap->conteudo_json);
        $graph['nodes'] = array_values(array_filter($graph['nodes'] ?? [], fn ($node) => ($node['id'] ?? null) !== $nodeId));
        $graph['connections'] = array_values(array_filter($graph['connections'] ?? [], function ($connection) use ($nodeId) {
            $source = $connection['from'] ?? $connection['source'] ?? $connection['sourceId'] ?? null;
            $target = $connection['to'] ?? $connection['target'] ?? $connection['targetId'] ?? null;

            return $source !== $nodeId && $target !== $nodeId;
        }));

        $this->assertRoadmapPayload($graph);

        $roadmap->conteudo_json = $this->normalize($graph);
        $roadmap->save();

        return response()->json([
            'success' => true,
            'data' => $roadmap->conteudo_json,
        ]);
    }

    public function submitActivity(Request $request, $turmaId, $roadmapId, string $nodeId)
    {
        $turma = Turma::with('alunos')->findOrFail($turmaId);
        $roadmap = Roadmap::where('turma_id', $turmaId)->findOrFail($roadmapId);

        $this->authorizeAccess($turma);
        abort_unless(Auth::user()->role === 'aluno', 403);

        $node = $this->findRoadmapNode($roadmap, $nodeId);
        abort_unless(($node['type'] ?? null) === 'activity', 404);

        $validated = $request->validate([
            'conteudo' => ['nullable', 'string', 'max:5000'],
            'arquivos' => ['nullable', 'array', 'max:5'],
            'arquivos.*' => ['file', 'max:10240', 'mimes:pdf,doc,docx,txt,md,rtf,csv,json,xml,html,odt,jpg,jpeg,png,gif,webp'],
        ], [
            'arquivos.*.mimes' => 'Envie arquivos em formatos aceitos pela plataforma.',
            'arquivos.*.max' => 'Cada arquivo deve ter no máximo 10 MB.',
        ]);

        $files = $request->file('arquivos', []);
        $content = trim((string) ($validated['conteudo'] ?? ''));

        if ($content === '' && count($files) === 0) {
            throw ValidationException::withMessages([
                'conteudo' => 'Envie uma descrição ou anexe pelo menos um arquivo.',
            ]);
        }

        $activity = Atividade::updateOrCreate(
            [
                'roadmap_id' => $roadmap->id,
                'roadmap_node_id' => $nodeId,
            ],
            [
                'titulo' => Str::limit((string) ($node['title'] ?? 'Atividade'), 255, ''),
                'descricao' => Str::limit((string) ($node['content'] ?? ''), 5000, ''),
                'anexos' => $node['activityAttachments'] ?? [],
            ]
        );

        if ($this->activityPastDue($activity)) {
            throw ValidationException::withMessages([
                'conteudo' => 'O prazo desta atividade terminou e novos envios foram bloqueados.',
            ]);
        }

        $storedFiles = [];
        $safeNodeFolder = Str::slug($nodeId) ?: (string) Str::uuid();

        foreach ($files as $file) {
            $path = $file->store("atividade-submissoes/{$roadmap->id}/{$safeNodeFolder}", 'local');
            $storedFiles[] = [
                'path' => $path,
                'name' => $file->getClientOriginalName(),
                'mime' => $file->getClientMimeType(),
                'size' => $file->getSize(),
            ];
        }

        Submissao::create([
            'atividade_id' => $activity->id,
            'user_id' => Auth::id(),
            'conteudo' => $content,
            'arquivo' => $storedFiles[0]['path'] ?? null,
            'arquivos' => $storedFiles,
        ]);

        return back()->with('success', 'Atividade enviada com sucesso.');
    }

    public function updateActivity(Request $request, $turmaId, $roadmapId, string $nodeId)
    {
        $turma = Turma::findOrFail($turmaId);
        $roadmap = Roadmap::where('turma_id', $turmaId)->findOrFail($roadmapId);

        $this->authorizeActivityReviewer($turma);

        $node = $this->findRoadmapNode($roadmap, $nodeId);
        abort_unless(($node['type'] ?? null) === 'activity', 404);

        $validated = $request->validate([
            'data_entrega' => ['nullable', 'date'],
        ]);

        Atividade::updateOrCreate(
            [
                'roadmap_id' => $roadmap->id,
                'roadmap_node_id' => $nodeId,
            ],
            [
                'titulo' => Str::limit((string) ($node['title'] ?? 'Atividade'), 255, ''),
                'descricao' => Str::limit((string) ($node['content'] ?? ''), 5000, ''),
                'anexos' => $node['activityAttachments'] ?? [],
                'data_entrega' => $validated['data_entrega'] ?? null,
            ]
        );

        return back()->with('success', 'Prazo da atividade atualizado.');
    }

    public function gradeActivitySubmission(Request $request, $turmaId, $roadmapId, $submissionId)
    {
        $turma = Turma::findOrFail($turmaId);
        $roadmap = Roadmap::where('turma_id', $turmaId)->findOrFail($roadmapId);

        $this->authorizeActivityReviewer($turma);

        $submission = Submissao::with('atividade')
            ->whereHas('atividade', fn ($query) => $query->where('roadmap_id', $roadmap->id))
            ->findOrFail($submissionId);

        $validated = $request->validate([
            'nota' => ['nullable', 'integer', 'min:0', 'max:100'],
        ]);

        $submission->update([
            'nota' => $validated['nota'] ?? null,
        ]);

        return back()->with('success', 'Nota da submissão atualizada.');
    }

    public function downloadActivitySubmissionFile($turmaId, $roadmapId, $submissionId, int $fileIndex)
    {
        $turma = Turma::with('alunos')->findOrFail($turmaId);
        $roadmap = Roadmap::where('turma_id', $turmaId)->findOrFail($roadmapId);

        $submission = Submissao::with('atividade')
            ->whereHas('atividade', fn ($query) => $query->where('roadmap_id', $roadmap->id))
            ->findOrFail($submissionId);

        $user = Auth::user();
        $canReview = $this->canReviewActivities($turma);
        $canDownloadOwnFile = $user->role === 'aluno'
            && (int) $submission->user_id === (int) $user->id
            && $turma->alunos->contains($user->id);

        abort_unless($canReview || $canDownloadOwnFile, 403);

        $files = collect($submission->arquivos ?? []);

        if ($files->isEmpty() && filled($submission->arquivo)) {
            $files = collect([[
                'path' => $submission->arquivo,
                'name' => basename($submission->arquivo),
            ]]);
        }

        $file = $files->values()->get($fileIndex);
        abort_unless(is_array($file) && filled($file['path'] ?? null), 404);

        $path = (string) $file['path'];
        abort_unless(Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->download($path, $file['name'] ?? basename($path));
    }

    private function normalize($data)
    {
        $this->assertRoadmapPayload($data);

        if (is_string($data)) {
            $decoded = json_decode($data, true);

            $data = json_last_error() === JSON_ERROR_NONE ? $decoded : [];
        }

        $data = $this->sanitizeRoadmapValue($data ?? []);

        return json_encode($data ?? [], JSON_UNESCAPED_UNICODE);
    }

    private function editableRoadmap($turmaId, $roadmapId): array
    {
        $turma = Turma::findOrFail($turmaId);
        $roadmap = Roadmap::where('turma_id', $turmaId)->findOrFail($roadmapId);

        $this->authorizeProfessorEditor($turma);

        return [$turma, $roadmap];
    }

    private function decodeGraph($data): array
    {
        if (is_string($data)) {
            $decoded = json_decode($data, true);
            $data = json_last_error() === JSON_ERROR_NONE ? $decoded : [];
        }

        return is_array($data) ? $data : [];
    }

    private function extractImportedGraph(mixed $payload): array
    {
        if (! is_array($payload)) {
            throw ValidationException::withMessages([
                'roadmap_file' => 'Não foi possível ler o arquivo. Verifique se ele é um JSON exportado pela UniRoad.',
            ]);
        }

        $graph = $payload['conteudo']
            ?? $payload['conteudo_json']
            ?? $payload['roadmap_data']
            ?? $payload['data']
            ?? $payload['roadmap']
            ?? $payload;

        if (is_string($graph)) {
            $decoded = json_decode($graph, true);
            $graph = json_last_error() === JSON_ERROR_NONE ? $decoded : null;
        }

        if (! is_array($graph) || ! $this->looksLikeRoadmapGraph($graph)) {
            throw ValidationException::withMessages([
                'roadmap_file' => 'Não foi possível ler o arquivo. Verifique se ele é um JSON exportado pela UniRoad.',
            ]);
        }

        if (isset($graph['modules']) && is_array($graph['modules'])) {
            $graph['modules'] = array_values(array_map(function ($module, $index) {
                $module = is_array($module) ? $module : [];

                return [
                    'id' => isset($module['id']) && is_string($module['id']) && $module['id'] !== ''
                        ? Str::limit($module['id'], 120, '')
                        : 'module-'.($index + 1),
                    'title' => isset($module['title']) && is_string($module['title']) && $module['title'] !== ''
                        ? Str::limit($module['title'], 120, '')
                        : 'Módulo '.($index + 1),
                    'nodes' => is_array($module['nodes'] ?? null) ? array_values($module['nodes']) : [],
                    'connections' => is_array($module['connections'] ?? null) ? array_values($module['connections']) : [],
                    'viewport' => is_array($module['viewport'] ?? null) ? $module['viewport'] : ['x' => 0, 'y' => 0, 'scale' => 1],
                ];
            }, array_values($graph['modules']), array_keys(array_values($graph['modules']))));

            if ($graph['modules'] === []) {
                throw ValidationException::withMessages([
                    'roadmap_file' => 'Não foi possível ler o arquivo. Verifique se ele é um JSON exportado pela UniRoad.',
                ]);
            }

            $graph['activeModuleId'] = isset($graph['activeModuleId']) && is_string($graph['activeModuleId'])
                ? $graph['activeModuleId']
                : ($graph['modules'][0]['id'] ?? 'module-1');
            $graph['nodes'] = is_array($graph['nodes'] ?? null) ? array_values($graph['nodes']) : $graph['modules'][0]['nodes'];
            $graph['connections'] = is_array($graph['connections'] ?? null) ? array_values($graph['connections']) : $graph['modules'][0]['connections'];
            $graph['viewport'] = is_array($graph['viewport'] ?? null) ? $graph['viewport'] : $graph['modules'][0]['viewport'];
        } else {
            $graph = [
                'nodes' => is_array($graph['nodes'] ?? null) ? array_values($graph['nodes']) : [],
                'connections' => is_array($graph['connections'] ?? null) ? array_values($graph['connections']) : [],
                'modules' => [[
                    'id' => 'module-1',
                    'title' => 'Módulo 1',
                    'nodes' => is_array($graph['nodes'] ?? null) ? array_values($graph['nodes']) : [],
                    'connections' => is_array($graph['connections'] ?? null) ? array_values($graph['connections']) : [],
                    'viewport' => is_array($graph['viewport'] ?? null) ? $graph['viewport'] : ['x' => 0, 'y' => 0, 'scale' => 1],
                ]],
                'activeModuleId' => 'module-1',
                'viewport' => is_array($graph['viewport'] ?? null) ? $graph['viewport'] : ['x' => 0, 'y' => 0, 'scale' => 1],
            ];
        }

        $this->assertRoadmapPayload($graph);

        return $graph;
    }

    private function activityContextForViewer(Roadmap $roadmap, Turma $turma): array
    {
        $user = Auth::user();
        $canReview = $this->canReviewActivities($turma);
        $nodes = collect($this->roadmapNodes($roadmap))
            ->filter(fn ($node) => is_array($node) && ($node['type'] ?? null) === 'activity')
            ->keyBy(fn ($node) => (string) ($node['id'] ?? ''))
            ->filter(fn ($node, $nodeId) => filled($nodeId));

        $activities = Atividade::with(['submissoes.aluno'])
            ->where('roadmap_id', $roadmap->id)
            ->get()
            ->keyBy(fn (Atividade $atividade) => (string) $atividade->roadmap_node_id);

        $activityPayloads = [];
        $studentSubmissions = [];
        $staffSubmissions = [];

        foreach ($nodes as $nodeId => $node) {
            $activity = $activities->get($nodeId);

            $activityPayloads[$nodeId] = $this->activityPayload($turma, $roadmap, $nodeId, $node, $activity, $canReview);

            if ($user->role === 'aluno' && $activity) {
                $submission = $activity->submissoes
                    ->where('user_id', $user->id)
                    ->sortByDesc('created_at')
                    ->first();

                if ($submission) {
                    $studentSubmissions[$nodeId] = $this->submissionPayload($turma, $roadmap, $submission, false);
                }
            }

            if ($canReview && $activity) {
                $staffSubmissions[$nodeId] = $activity->submissoes
                    ->sortByDesc('created_at')
                    ->values()
                    ->map(fn (Submissao $submission) => $this->submissionPayload($turma, $roadmap, $submission, true))
                    ->all();
            }
        }

        return [
            'canReview' => $canReview,
            'activities' => $activityPayloads,
            'studentSubmissions' => $studentSubmissions,
            'staffSubmissions' => $staffSubmissions,
        ];
    }

    private function activityPayload(Turma $turma, Roadmap $roadmap, string $nodeId, array $node, ?Atividade $activity, bool $canReview): array
    {
        $dueDate = $activity?->data_entrega;

        return [
            'id' => $activity?->id,
            'nodeId' => $nodeId,
            'title' => $activity?->titulo ?: (string) ($node['title'] ?? 'Atividade'),
            'description' => $activity?->descricao ?: (string) ($node['content'] ?? ''),
            'attachments' => $activity?->anexos ?: ($node['activityAttachments'] ?? []),
            'dueDate' => $dueDate?->format('Y-m-d'),
            'dueDateLabel' => $dueDate?->format('d/m/Y'),
            'isPastDue' => $this->activityPastDue($activity),
            'createdAt' => optional($activity?->created_at)->format('d/m/Y H:i'),
            'updatedAt' => optional($activity?->updated_at)->format('d/m/Y H:i'),
            'updateUrl' => $canReview ? route('roadmaps.activities.update', [$turma->id, $roadmap->id, $nodeId]) : null,
        ];
    }

    private function submissionPayload(Turma $turma, Roadmap $roadmap, Submissao $submission, bool $canGrade): array
    {
        return [
            'id' => $submission->id,
            'studentName' => $submission->aluno?->name ?? 'Aluno removido',
            'studentEmail' => $submission->aluno?->email,
            'content' => $submission->conteudo,
            'grade' => $submission->nota,
            'createdAt' => optional($submission->created_at)->format('d/m/Y H:i'),
            'updatedAt' => optional($submission->updated_at)->format('d/m/Y H:i'),
            'sentAt' => optional($submission->created_at)->format('d/m/Y H:i'),
            'files' => $this->submissionFilesPayload($turma, $roadmap, $submission),
            'gradeUrl' => $canGrade ? route('roadmaps.activities.submissions.grade', [$turma->id, $roadmap->id, $submission->id]) : null,
        ];
    }

    private function submissionFilesPayload(Turma $turma, Roadmap $roadmap, Submissao $submission): array
    {
        $files = collect($submission->arquivos ?? []);

        if ($files->isEmpty() && filled($submission->arquivo)) {
            $files = collect([[
                'path' => $submission->arquivo,
                'name' => basename($submission->arquivo),
                'size' => null,
            ]]);
        }

        return $files
            ->values()
            ->map(fn ($file, $index) => [
                'name' => $file['name'] ?? 'Arquivo enviado',
                'size' => $file['size'] ?? null,
                'url' => route('roadmaps.activities.submissions.download', [$turma->id, $roadmap->id, $submission->id, $index]),
            ])
            ->all();
    }

    private function activityPastDue(?Atividade $activity): bool
    {
        return $activity?->data_entrega
            ? now()->startOfDay()->gt($activity->data_entrega)
            : false;
    }

    private function roadmapNodes(Roadmap $roadmap): array
    {
        $graph = $this->decodeGraph($roadmap->conteudo_json);
        $nodes = [];

        if (isset($graph['nodes']) && is_array($graph['nodes'])) {
            $nodes = array_merge($nodes, $graph['nodes']);
        }

        foreach (($graph['modules'] ?? []) as $module) {
            if (is_array($module) && isset($module['nodes']) && is_array($module['nodes'])) {
                $nodes = array_merge($nodes, $module['nodes']);
            }
        }

        return collect($nodes)
            ->filter(fn ($node) => is_array($node) && filled($node['id'] ?? null))
            ->unique(fn ($node) => (string) $node['id'])
            ->values()
            ->all();
    }

    private function findRoadmapNode(Roadmap $roadmap, string $nodeId): ?array
    {
        $graph = $this->decodeGraph($roadmap->conteudo_json);
        $groups = [];

        if (isset($graph['nodes']) && is_array($graph['nodes'])) {
            $groups[] = $graph['nodes'];
        }

        foreach (($graph['modules'] ?? []) as $module) {
            if (is_array($module) && isset($module['nodes']) && is_array($module['nodes'])) {
                $groups[] = $module['nodes'];
            }
        }

        foreach ($groups as $nodes) {
            foreach ($nodes as $node) {
                if (is_array($node) && (string) ($node['id'] ?? '') === $nodeId) {
                    return $node;
                }
            }
        }

        return null;
    }

    private function looksLikeRoadmapGraph(array $graph): bool
    {
        if (array_key_exists('modules', $graph)) {
            return is_array($graph['modules']);
        }

        return array_key_exists('nodes', $graph)
            && is_array($graph['nodes'])
            && (! array_key_exists('connections', $graph) || is_array($graph['connections']));
    }

    private function extractImportedTitle(array $payload, string $filename): string
    {
        $title = $payload['titulo'] ?? $payload['title'] ?? null;

        if (! is_string($title) || trim($title) === '') {
            $title = pathinfo($filename, PATHINFO_FILENAME) ?: 'Roadmap importado';
        }

        return Str::limit(trim(strip_tags($title)), 255, '');
    }

    private function extractImportedDescription(array $payload): ?string
    {
        $description = $payload['descricao'] ?? $payload['description'] ?? null;

        if (! is_string($description) || trim($description) === '') {
            return 'Roadmap importado de arquivo JSON.';
        }

        return Str::limit(trim(strip_tags($description)), 2000, '');
    }

    private function roadmapPayloadRule(): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail): void {
            try {
                $this->assertRoadmapPayload($value);
            } catch (ValidationException $exception) {
                $fail($exception->getMessage());
            }
        };
    }

    private function assertRoadmapPayload(mixed $payload): void
    {
        $decoded = $payload;

        if (is_string($payload)) {
            if (strlen($payload) > self::MAX_ROADMAP_JSON_BYTES) {
                throw ValidationException::withMessages(['roadmap' => 'O roadmap excede o tamanho permitido.']);
            }

            $decoded = json_decode($payload, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw ValidationException::withMessages(['roadmap' => 'O JSON do roadmap é inválido.']);
            }
        }

        if (! is_array($decoded) && $decoded !== null) {
            throw ValidationException::withMessages(['roadmap' => 'A estrutura do roadmap é inválida.']);
        }

        $encoded = json_encode($decoded ?? [], JSON_UNESCAPED_UNICODE);
        if ($encoded === false || strlen($encoded) > self::MAX_ROADMAP_JSON_BYTES) {
            throw ValidationException::withMessages(['roadmap' => 'O roadmap excede o tamanho permitido.']);
        }

        $this->assertRoadmapStructure($decoded ?? []);
    }

    private function assertRoadmapStructure(mixed $value, int $depth = 0): void
    {
        if ($depth > self::MAX_ROADMAP_DEPTH) {
            throw ValidationException::withMessages(['roadmap' => 'O roadmap possui profundidade inválida.']);
        }

        if (is_string($value) && strlen($value) > self::MAX_ROADMAP_STRING_BYTES) {
            throw ValidationException::withMessages(['roadmap' => 'Um campo do roadmap excede o tamanho permitido.']);
        }

        if (! is_array($value)) {
            return;
        }

        if (count($value) > self::MAX_ROADMAP_ARRAY_ITEMS) {
            throw ValidationException::withMessages(['roadmap' => 'O roadmap possui itens demais.']);
        }

        if (isset($value['nodes']) && is_array($value['nodes']) && count($value['nodes']) > self::MAX_ROADMAP_NODES) {
            throw ValidationException::withMessages(['roadmap' => 'O roadmap possui blocos demais.']);
        }

        if (isset($value['connections']) && is_array($value['connections']) && count($value['connections']) > self::MAX_ROADMAP_CONNECTIONS) {
            throw ValidationException::withMessages(['roadmap' => 'O roadmap possui conexões demais.']);
        }

        foreach ($value as $child) {
            $this->assertRoadmapStructure($child, $depth + 1);
        }
    }

    private function sanitizeRoadmapValue(mixed $value): mixed
    {
        if (is_string($value)) {
            $value = str_replace("\0", '', $value);
            $value = preg_replace('#<\s*script\b[^>]*>.*?<\s*/\s*script\s*>#is', '', $value) ?? $value;
            $value = preg_replace('#<\s*/?\s*script\b[^>]*>#i', '', $value) ?? $value;

            return strip_tags($value);
        }

        if (! is_array($value)) {
            return $value;
        }

        $sanitized = [];
        foreach ($value as $key => $child) {
            $sanitized[$key] = $this->sanitizeRoadmapValue($child);
        }

        return $sanitized;
    }

    private function authorizeAccess($turma)
    {
        $user = Auth::user();

        $allowed =
            $user->role === 'admin' ||
            $turma->docente_id === $user->id ||
            ($user->role === 'aluno' && $turma->alunos->contains($user->id));

        if (! $allowed) {
            abort(403);
        }
    }

    private function authorizeProfessorEditor($turma)
    {
        $user = Auth::user();

        if ($user->role !== 'docente' || $turma->docente_id !== $user->id) {
            abort(403);
        }
    }

    private function authorizeRoadmapDelete($turma)
    {
        $user = Auth::user();

        if ($user->role !== 'admin' && ($user->role !== 'docente' || $turma->docente_id !== $user->id)) {
            abort(403);
        }
    }

    private function authorizeActivityReviewer($turma): void
    {
        if (! $this->canReviewActivities($turma)) {
            abort(403);
        }
    }

    private function canReviewActivities($turma): bool
    {
        $user = Auth::user();

        return $user->role === 'admin'
            || ($user->role === 'docente' && (int) $turma->docente_id === (int) $user->id);
    }
}
