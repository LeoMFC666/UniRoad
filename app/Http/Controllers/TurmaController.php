<?php

namespace App\Http\Controllers;

use App\Models\Turma;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class TurmaController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->role === 'admin') {
            return redirect()->route('admin.gerenciamento-turmas');
        }

        if ($user->role === 'docente') {
            return redirect()->route('docente.minhas-turmas');
        }

        $turmas = Turma::with('docente')
            ->whereHas('alunos', fn ($alunos) => $alunos->where('users.id', $user->id))
            ->get();

        return view('turmas.index', compact('turmas'));
    }

    public function create()
    {
        $docentes = Auth::user()->role === 'admin'
            ? User::where('role', 'docente')->orderBy('name')->get()
            : collect();

        return view('turmas.create', compact('docentes'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'descricao' => ['nullable', 'string', 'max:2000'],
            'docente_id' => [
                Rule::requiredIf($user->role === 'admin'),
                'nullable',
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('role', 'docente')),
            ],
        ]);

        Turma::create([
            'nome' => $validated['nome'],
            'descricao' => $validated['descricao'] ?? null,
            'docente_id' => $user->role === 'admin' ? $validated['docente_id'] : $user->id,
        ]);

        return redirect()->route('turmas.index')->with('success', 'Turma criada com sucesso!');
    }

    public function show($id)
    {
        $turma = Turma::with(['docente', 'alunos'])->findOrFail($id);

        $this->authorizeTurmaAccess($turma);

        return view('turmas.show', compact('turma'));
    }

    public function manage($id)
    {
        $turma = Turma::with(['docente', 'alunos'])->findOrFail($id);

        $this->authorizeTurmaManagement($turma);

        return view('turmas.manage', compact('turma'));
    }

    public function addAluno(Request $request, $turmaId)
    {
        $turma = Turma::findOrFail($turmaId);

        $this->authorizeTurmaManagement($turma);

        $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        $aluno = User::where('email', $request->email)
            ->where('role', 'aluno')
            ->first();

        if (! $aluno) {
            return back()->withErrors(['email' => 'Aluno não encontrado no sistema']);
        }

        if ($turma->alunos()->where('users.id', $aluno->id)->exists()) {
            return back()->withErrors(['email' => 'Aluno já está inscrito nesta turma.']);
        }

        $turma->alunos()->attach($aluno->id);

        return back()->with('success', 'Aluno adicionado com sucesso!');
    }

    public function removeAluno($turmaId, $alunoId)
    {
        $turma = Turma::findOrFail($turmaId);

        $this->authorizeTurmaManagement($turma);

        abort_unless(User::whereKey($alunoId)->where('role', 'aluno')->exists(), 404);

        $turma->alunos()->detach($alunoId);

        return back()->with('success', 'Aluno removido com sucesso!');
    }

    public function destroy(Turma $turma)
    {
        $this->authorizeTurmaManagement($turma);

        DB::transaction(function () use ($turma) {
            $turma->alunos()->detach();
            $turma->roadmaps()->delete();
            $turma->delete();
        });

        $redirectRoute = Auth::user()->role === 'admin'
            ? 'admin.gerenciamento-turmas'
            : 'docente.minhas-turmas';

        return redirect()
            ->route($redirectRoute)
            ->with('success', 'Turma excluída com sucesso.');
    }

    private function authorizeTurmaManagement(Turma $turma): void
    {
        $user = Auth::user();

        if ($user->role !== 'admin' && ($user->role !== 'docente' || $turma->docente_id !== $user->id)) {
            abort(403, 'Acesso negado.');
        }
    }

    private function authorizeTurmaAccess(Turma $turma): void
    {
        $user = Auth::user();

        $allowed = $user->role === 'admin'
            || ($user->role === 'docente' && $turma->docente_id === $user->id)
            || ($user->role === 'aluno' && $turma->alunos->contains($user->id));

        if (! $allowed) {
            abort(403, 'Acesso negado.');
        }
    }
}
