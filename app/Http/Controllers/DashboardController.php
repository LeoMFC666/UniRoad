<?php

namespace App\Http\Controllers;

use App\Models\Turma;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        if ($user->role === 'docente') {
            return redirect()->route('dashboard-docente');
        }

        if ($user->role === 'admin') {
            return redirect()->route('dashboard-admin');
        }

        return $this->alunoDashboard($request);
    }

    public function alunoDashboard(Request $request)
    {
        $user = Auth::user();
        $turmas = $user->turmas;

        return view('dashboard', compact('turmas'));
    }

    public function docenteDashboard()
    {
        $user = Auth::user();
        $turmas = $user->turmasDocente;

        return view('dashboard.docente', compact('turmas'));
    }

    public function adminDashboard()
    {
        $turmas = Turma::with('docente')->get();
        $docentes = User::where('role', 'docente')->get();
        $alunos = User::where('role', 'aluno')->get();

        return view('dashboard.admin', compact('turmas', 'docentes', 'alunos'));
    }

    public function roadmaps()
    {
        $user = Auth::user();
        $turmas = $user->turmas;

        return view('aluno.roadmaps', compact('turmas'));
    }

    public function cursosDisponiveis(Request $request)
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
        ]);
        $query = $validated['search'] ?? null;
        $available = Turma::with('docente')
            ->when($query, fn($q) => $q->where('nome', 'like', "%{$query}%"))
            ->get();

        return view('cursos.index', compact('available', 'query'));
    }

    public function docenteTurmas()
    {
        $user = Auth::user();
        $turmas = $user->role === 'docente'
            ? $user->turmasDocente
            : Turma::with('docente')->get();

        return view('docente.minhas-turmas', compact('turmas'));
    }

    public function adminTurmas()
    {
        $turmas = Turma::with('docente')
            ->withCount(['alunos', 'roadmaps'])
            ->orderBy('nome')
            ->get();

        return view('admin.gerenciamento-turmas', compact('turmas'));
    }

    public function adminDocentes()
    {
        $docentes = User::where('role', 'docente')
            ->withCount('turmasDocente')
            ->orderBy('name')
            ->get();
        $turmas = Turma::with('docente')->orderBy('nome')->get();

        return view('admin.gerenciamento-docentes', compact('docentes', 'turmas'));
    }

    public function adminAlunos()
    {
        $alunos = User::where('role', 'aluno')
            ->with('turmas:id,nome')
            ->withCount('turmas')
            ->orderBy('name')
            ->get();
        $turmas = Turma::with('docente')
            ->withCount('alunos')
            ->orderBy('nome')
            ->get();

        return view('admin.gerenciamento-alunos', compact('alunos', 'turmas'));
    }

    public function assignProfessorToTurma(Request $request)
    {
        $validated = $request->validate([
            'turma_id' => ['required', 'exists:turmas,id'],
            'docente_id' => [
                'required',
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('role', 'docente')),
            ],
        ]);

        $turma = Turma::findOrFail($validated['turma_id']);
        $turma->update(['docente_id' => $validated['docente_id']]);

        return back()->with('success', 'Professor vinculado à turma com sucesso!');
    }

    public function promoteUserToProfessor(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255', 'exists:users,email'],
        ], [
            'email.exists' => 'Usuário não encontrado no sistema.',
        ]);

        $user = User::where('email', $validated['email'])->firstOrFail();

        if ($user->role === 'admin') {
            return back()
                ->withErrors(['email' => 'Administradores não podem ser convertidos em professor por este formulário.'])
                ->withInput();
        }

        if ($user->role === 'docente') {
            return back()->with('success', 'Este usuário já está cadastrado como professor.');
        }

        $user->update(['role' => 'docente']);

        return back()->with('success', 'Usuário promovido a professor com sucesso.');
    }

    public function assignAlunoToTurma(Request $request)
    {
        $validated = $request->validate([
            'turma_id' => ['required', 'exists:turmas,id'],
            'aluno_id' => [
                'required',
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('role', 'aluno')),
            ],
        ]);

        $turma = Turma::findOrFail($validated['turma_id']);

        if ($turma->alunos()->where('users.id', $validated['aluno_id'])->exists()) {
            return back()->withErrors(['aluno_id' => 'Este aluno já está vinculado a esta turma.']);
        }

        $turma->alunos()->attach($validated['aluno_id']);

        return back()->with('success', 'Aluno adicionado à turma com sucesso.');
    }

    public function moveAlunoBetweenTurmas(Request $request)
    {
        $validated = $request->validate([
            'aluno_id' => [
                'required',
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('role', 'aluno')),
            ],
            'from_turma_id' => ['required', 'exists:turmas,id'],
            'to_turma_id' => ['required', 'different:from_turma_id', 'exists:turmas,id'],
        ], [
            'to_turma_id.different' => 'Escolha uma turma de destino diferente da turma atual.',
        ]);

        $fromTurma = Turma::findOrFail($validated['from_turma_id']);
        $toTurma = Turma::findOrFail($validated['to_turma_id']);

        if (! $fromTurma->alunos()->where('users.id', $validated['aluno_id'])->exists()) {
            return back()->withErrors(['from_turma_id' => 'O aluno selecionado não está vinculado à turma de origem.']);
        }

        DB::transaction(function () use ($fromTurma, $toTurma, $validated) {
            $fromTurma->alunos()->detach($validated['aluno_id']);
            $toTurma->alunos()->syncWithoutDetaching([$validated['aluno_id']]);
        });

        return back()->with('success', 'Aluno remanejado com sucesso.');
    }

    public function destroyProfessor(Request $request, User $professor)
    {
        abort_unless($professor->role === 'docente', 404);

        $ownedTurmas = $professor->turmasDocente()->count();

        if ($ownedTurmas > 0) {
            $validated = $request->validate([
                'replacement_docente_id' => [
                    'required',
                    Rule::exists('users', 'id')->where(fn ($query) => $query
                        ->where('role', 'docente')
                        ->where('id', '!=', $professor->id)),
                ],
            ]);

            Turma::where('docente_id', $professor->id)
                ->update(['docente_id' => $validated['replacement_docente_id']]);
        }

        $professor->delete();

        return back()->with('success', 'Professor removido com segurança.');
    }

    public function destroyAluno(User $aluno)
    {
        abort_unless($aluno->role === 'aluno', 404);

        DB::transaction(function () use ($aluno) {
            $aluno->turmas()->detach();
            $aluno->delete();
        });

        return back()->with('success', 'Aluno removido do sistema com segurança.');
    }

    public function destroyTurma(Turma $turma)
    {
        DB::transaction(function () use ($turma) {
            $turma->alunos()->detach();
            $turma->roadmaps()->delete();
            $turma->delete();
        });

        return redirect()
            ->route('admin.gerenciamento-turmas')
            ->with('success', 'Turma excluída com sucesso.');
    }
}
