<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TurmaController;
use App\Http\Controllers\RoadmapController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\SupportController;

/*
|--------------------------------------------------------------------------
| ROTA INICIAL
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
});

Route::view('/sobre', 'sobre')->name('sobre');
Route::get('/contato', [ContactController::class, 'create'])->name('contato');
Route::post('/contato', [ContactController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('contato.store');

/*
|--------------------------------------------------------------------------
| PAINEL DINÂMICO
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/suporte', [SupportController::class, 'create'])->name('support.create');
    Route::post('/suporte', [SupportController::class, 'store'])
        ->middleware('throttle:5,1')
        ->name('support.store');

    Route::get('/dashboard/aluno', [DashboardController::class, 'alunoDashboard'])
        ->middleware('role:aluno')
        ->name('dashboard-aluno');

    Route::get('/dashboard/docente', [DashboardController::class, 'docenteDashboard'])
        ->middleware('role:docente')
        ->name('dashboard-docente');

    Route::get('/dashboard/admin', [DashboardController::class, 'adminDashboard'])
        ->middleware('role:admin')
        ->name('dashboard-admin');

    Route::get('/roadmaps', [DashboardController::class, 'roadmaps'])
        ->middleware('role:aluno')
        ->name('roadmaps');

    Route::get('/cursos', [DashboardController::class, 'cursosDisponiveis'])
        ->middleware('role:admin')
        ->name('cursos');

    Route::get('/docente/minhas-turmas', [DashboardController::class, 'docenteTurmas'])
        ->middleware('role:docente,admin')
        ->name('docente.minhas-turmas');

    Route::get('/admin/gerenciamento-turmas', [DashboardController::class, 'adminTurmas'])
        ->middleware('role:admin')
        ->name('admin.gerenciamento-turmas');

    Route::get('/admin/gerenciamento-docentes', [DashboardController::class, 'adminDocentes'])
        ->middleware('role:admin')
        ->name('admin.gerenciamento-docentes');

    Route::get('/admin/gerenciamento-alunos', [DashboardController::class, 'adminAlunos'])
        ->middleware('role:admin')
        ->name('admin.gerenciamento-alunos');

    Route::post('/admin/turmas/professor', [DashboardController::class, 'assignProfessorToTurma'])
        ->middleware('role:admin')
        ->name('admin.turmas.professor');

    Route::post('/admin/professores', [DashboardController::class, 'promoteUserToProfessor'])
        ->middleware('role:admin')
        ->name('admin.professores.store');

    Route::post('/admin/turmas/aluno', [DashboardController::class, 'assignAlunoToTurma'])
        ->middleware('role:admin')
        ->name('admin.turmas.aluno');

    Route::post('/admin/turmas/aluno/remanejar', [DashboardController::class, 'moveAlunoBetweenTurmas'])
        ->middleware('role:admin')
        ->name('admin.turmas.aluno.remanejar');

    Route::delete('/admin/turmas/{turma}', [DashboardController::class, 'destroyTurma'])
        ->middleware('role:admin')
        ->whereNumber('turma')
        ->name('admin.turmas.destroy');

    Route::delete('/admin/professores/{professor}', [DashboardController::class, 'destroyProfessor'])
        ->middleware('role:admin')
        ->whereNumber('professor')
        ->name('admin.professores.destroy');

    Route::delete('/admin/alunos/{aluno}', [DashboardController::class, 'destroyAluno'])
        ->middleware('role:admin')
        ->whereNumber('aluno')
        ->name('admin.alunos.destroy');
});

/*
|--------------------------------------------------------------------------
| TURMAS (SOMENTE DOCENTE/ADMIN)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:docente,admin'])->group(function () {

    Route::get('/turmas/create', [TurmaController::class, 'create'])
        ->name('turmas.create');
    Route::post('/turmas', [TurmaController::class, 'store'])
        ->middleware('throttle:20,1')
        ->name('turmas.store');
    Route::get('/turmas/{id}/manage', [TurmaController::class, 'manage'])
        ->whereNumber('id')
        ->name('turmas.manage');
    Route::post('/turmas/{turmaId}/alunos', [TurmaController::class, 'addAluno'])
        ->whereNumber('turmaId')
        ->middleware('throttle:20,1')
        ->name('turmas.add-aluno');
    Route::delete('/turmas/{turmaId}/alunos/{alunoId}', [TurmaController::class, 'removeAluno'])
        ->whereNumber(['turmaId', 'alunoId'])
        ->middleware('throttle:30,1')
        ->name('turmas.remove-aluno');
    Route::delete('/turmas/{turma}', [TurmaController::class, 'destroy'])
        ->whereNumber('turma')
        ->middleware('throttle:10,1')
        ->name('turmas.destroy');

});

/*
|--------------------------------------------------------------------------
| TURMAS (LISTAR + VER)
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    Route::get('/turmas', [TurmaController::class, 'index'])
        ->name('turmas.index');
    Route::get('/turmas/{id}', [TurmaController::class, 'show'])
        ->whereNumber('id')
        ->name('turmas.show');
});

/*
|--------------------------------------------------------------------------
| ROADMAPS (CORE)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {

    Route::get('/turmas/{turmaId}/roadmaps', [RoadmapController::class, 'index'])
        ->whereNumber('turmaId')
        ->name('roadmaps.index');

    Route::get('/turmas/{turmaId}/roadmaps/create', [RoadmapController::class, 'create'])
        ->middleware('role:docente')
        ->whereNumber('turmaId')
        ->name('roadmaps.create');

    Route::get('/turmas/{turmaId}/roadmaps/{roadmapId}', [RoadmapController::class, 'show'])
        ->whereNumber(['turmaId', 'roadmapId'])
        ->name('roadmaps.show');

    Route::get('/turmas/{turmaId}/roadmaps/{roadmapId}/edit', [RoadmapController::class, 'edit'])
        ->middleware('role:docente')
        ->whereNumber(['turmaId', 'roadmapId'])
        ->name('roadmaps.edit');

    Route::post('/turmas/{turmaId}/roadmaps/{roadmapId}/activities/{nodeId}/submissions', [RoadmapController::class, 'submitActivity'])
        ->middleware(['role:aluno', 'throttle:10,1'])
        ->whereNumber(['turmaId', 'roadmapId'])
        ->name('roadmaps.activities.submit');

    Route::get('/turmas/{turmaId}/roadmaps/{roadmapId}/activities/submissions/{submissionId}/files/{fileIndex}', [RoadmapController::class, 'downloadActivitySubmissionFile'])
        ->whereNumber(['turmaId', 'roadmapId', 'submissionId', 'fileIndex'])
        ->name('roadmaps.activities.submissions.download');
});

/*
|--------------------------------------------------------------------------
| ROADMAPS CRUD (TRADICIONAL)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:docente'])->group(function () {

    Route::post('/turmas/{turmaId}/roadmaps', [RoadmapController::class, 'store'])
        ->whereNumber('turmaId')
        ->middleware('throttle:20,1')
        ->name('roadmaps.store');

    Route::post('/turmas/{turmaId}/roadmaps/import', [RoadmapController::class, 'import'])
        ->whereNumber('turmaId')
        ->middleware('throttle:10,1')
        ->name('roadmaps.import');

    Route::put('/turmas/{turmaId}/roadmaps/{roadmapId}', [RoadmapController::class, 'update'])
        ->whereNumber(['turmaId', 'roadmapId'])
        ->middleware('throttle:30,1')
        ->name('roadmaps.update');

});

Route::delete('/turmas/{turmaId}/roadmaps/{roadmapId}', [RoadmapController::class, 'destroy'])
    ->middleware(['auth', 'role:docente,admin'])
    ->whereNumber(['turmaId', 'roadmapId'])
    ->name('roadmaps.destroy');

Route::patch('/turmas/{turmaId}/roadmaps/{roadmapId}/activities/{nodeId}', [RoadmapController::class, 'updateActivity'])
    ->middleware(['auth', 'role:docente,admin', 'throttle:30,1'])
    ->whereNumber(['turmaId', 'roadmapId'])
    ->name('roadmaps.activities.update');

Route::patch('/turmas/{turmaId}/roadmaps/{roadmapId}/activities/submissions/{submissionId}', [RoadmapController::class, 'gradeActivitySubmission'])
    ->middleware(['auth', 'role:docente,admin', 'throttle:60,1'])
    ->whereNumber(['turmaId', 'roadmapId', 'submissionId'])
    ->name('roadmaps.activities.submissions.grade');

/*
|--------------------------------------------------------------------------
| EDITOR PHP-FIRST
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:docente'])->group(function () {

    Route::post(
        '/turmas/{turmaId}/roadmaps/{roadmapId}/nodes',
        [RoadmapController::class, 'addNode']
    )->whereNumber(['turmaId', 'roadmapId'])
        ->middleware('throttle:60,1')
        ->name('roadmaps.nodes.add');

    Route::put(
        '/turmas/{turmaId}/roadmaps/{roadmapId}/nodes/{nodeId}',
        [RoadmapController::class, 'updateNode']
    )->whereNumber(['turmaId', 'roadmapId'])
        ->middleware('throttle:60,1')
        ->name('roadmaps.nodes.update');

    Route::delete(
        '/turmas/{turmaId}/roadmaps/{roadmapId}/nodes/{nodeId}',
        [RoadmapController::class, 'deleteNode']
    )->whereNumber(['turmaId', 'roadmapId'])
        ->middleware('throttle:60,1')
        ->name('roadmaps.nodes.delete');

    Route::post(
        '/turmas/{turmaId}/roadmaps/{roadmapId}/save',
        [RoadmapController::class, 'save']
    )->whereNumber(['turmaId', 'roadmapId'])
        ->middleware('throttle:30,1')
        ->name('roadmaps.save');
});

/*
|--------------------------------------------------------------------------
| PROFILE (BREEZE)
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::get('/conta/configuracoes', [ProfileController::class, 'settings'])->name('account.settings');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

/*
|--------------------------------------------------------------------------
| AUTH ROUTES
|--------------------------------------------------------------------------
*/

require __DIR__.'/auth.php';
