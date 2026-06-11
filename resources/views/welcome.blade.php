<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'UniRoad') }}</title>
        <link rel="icon" type="image/png" sizes="any" href="{{ asset('img/logo.png') }}">
        <link rel="shortcut icon" href="{{ asset('img/logo.png') }}">

        @fonts
        @ddfsnStyles

        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif

        <style>
            .landing-bg {
                background:
                    radial-gradient(circle at top left, rgba(34, 211, 197, 0.16), transparent 28rem),
                    radial-gradient(circle at top right, rgba(96, 165, 250, 0.14), transparent 30rem),
                    #080a10;
            }

            .nav-pill,
            .hero-panel {
                background: rgba(17, 21, 32, 0.88);
                border: 1px solid rgba(148, 163, 184, 0.16);
                border-radius: 8px;
            }

            .feature-chip {
                background: rgba(34, 211, 197, 0.12);
                border: 1px solid rgba(34, 211, 197, 0.28);
            }

            .demo-panel {
                background: transparent;
                border: 0;
                border-radius: 0;
            }

            .roadmap-demo.flow-container {
                --flow-bg-color: #080a10;
                --flow-bg-pattern-color: rgba(96, 165, 250, 0.22);
                --flow-container-height: 460px;
                --flow-edge-stroke: #60a5fa;
                --flow-edge-stroke-width: 3;
                --flow-edge-dot-fill: #22d3c5;
                --flow-handle-size: 0;
                --flow-node-bg: rgba(17, 21, 32, 0.96);
                --flow-node-border: 1px solid rgba(96, 165, 250, 0.46);
                --flow-node-border-top: 1px solid rgba(96, 165, 250, 0.46);
                --flow-node-border-radius: 8px;
                --flow-node-color: #f8fafc;
                --flow-node-padding: 0;
                --flow-node-shadow: 0 18px 50px rgba(0, 0, 0, 0.28);
                border: 1px solid rgba(96, 165, 250, 0.22);
                border-radius: 8px;
                cursor: grab;
            }

            .roadmap-demo.flow-container:active {
                cursor: grabbing;
            }

            .roadmap-demo .flow-edges path {
                filter: drop-shadow(0 0 8px rgba(96, 165, 250, 0.42));
                stroke-linecap: round;
            }

            .roadmap-demo .flow-edge-particle {
                filter: drop-shadow(0 0 12px rgba(34, 211, 197, 0.95));
            }

            .af-landing-node {
                align-items: center;
                box-shadow: 0 18px 50px rgba(0, 0, 0, 0.28);
                display: grid;
                justify-items: start;
                min-height: 94px;
                padding: 18px 20px;
                text-align: left;
                width: 200px;
            }

            .af-landing-node.is-start,
            .af-landing-node.is-end {
                border-radius: 999px;
                height: 94px;
                justify-items: center;
                text-align: center;
            }

            .af-landing-node.is-start {
                border-color: #22c55e;
            }

            .af-landing-node.is-task {
                border-color: #22d3c5;
                min-height: 178px;
                padding: 28px 32px;
                width: 330px;
            }

            .af-landing-node.is-end {
                border-color: #ef4444;
            }

            .demo-label {
                color: #93a4c6;
                font-size: 11px;
                font-weight: 900;
                letter-spacing: 0.12em;
                text-transform: uppercase;
            }

            .af-landing-node h3 {
                font-size: 20px;
                font-weight: 900;
                margin: 2px 0 8px;
            }

            .af-landing-node p {
                color: #cbd5e1;
                font-size: 13px;
                line-height: 1.45;
                margin: 0;
            }

            .demo-actions {
                display: grid;
                gap: 8px;
                margin-top: 14px;
            }

            .demo-action {
                background: linear-gradient(180deg, rgba(37, 99, 235, 0.18), rgba(15, 23, 42, 0.88));
                border: 1px solid rgba(96, 165, 250, 0.72);
                border-radius: 8px;
                color: #dbeafe;
                font-size: 12px;
                font-weight: 800;
                padding: 8px 10px;
                text-align: center;
            }

            @media (max-width: 900px) {
                .roadmap-demo.flow-container { --flow-container-height: 380px; }
                .af-landing-node.is-task { width: 290px; }
            }
        </style>
    </head>
    <body class="landing-bg min-h-screen text-white antialiased">
        <div class="relative overflow-hidden">
            <div class="relative z-10 max-w-7xl mx-auto px-6 py-8 lg:py-10">
                <header class="flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
                    <a href="{{ url('/') }}" class="inline-flex items-center gap-3">
                        <img src="{{ asset('img/logo.png') }}" alt="UniRoad logo" class="w-12 h-12 rounded-full border-white/10 shadow-2xl" />
                        <div>
                            <span class="font-semibold text-lg">UniRoad</span>
                            <div class="text-xs text-white/60">Aprenda, acompanhe e evolua</div>
                        </div>
                    </a>

                    @if (Route::has('login'))
                        <nav class="inline-flex flex-wrap justify-start gap-3 sm:justify-end w-full sm:w-auto">
                            <x-btn href="{{ route('login') }}" class="nav-pill rounded-full px-4 py-2 w-full sm:w-auto text-white !text-white">Entrar</x-btn>
                        </nav>
                    @endif
                </header>

                <main class="grid gap-10 lg:grid-cols-[0.82fr_1.18fr] items-start py-16 lg:py-20">
                    <section class="space-y-8 max-w-2xl mx-auto lg:mx-0 text-center lg:text-left">
                        <div class="inline-flex items-center justify-center lg:justify-start gap-2 rounded-full px-4 py-2 feature-chip text-sm text-white/80 font-medium">
                            <x-pulser style="info" class="inline-flex h-2 w-2.5" />
                            Plataforma para turmas, alunos e professores
                        </div>

                        <div class="space-y-6">
                            <h1 class="text-4xl font-extrabold tracking-tight sm:text-5xl lg:text-6xl">Roadmaps claros para cada jornada de aprendizado.</h1>
                            <p class="mx-auto max-w-xl text-lg leading-8 text-white/75 lg:mx-0">Organize turmas, acompanhe alunos e transforme planos de estudo em fluxos visuais fáceis de seguir.</p>
                        </div>

                        <div class="flex flex-col sm:flex-row justify-center lg:justify-start gap-4">
                            <x-btn href="{{ route('register') }}" style="primary" size="lg" class="w-full sm:w-auto">Cadastre-se</x-btn>
                            <div class="flex gap-2">
                                <x-btn href="{{ route('sobre') }}" style="primary" size="lg" class="nav-pill w-full sm:w-auto text-white !text-white">Saiba mais</x-btn>
                                <x-btn href="{{ route('sobre') }}" style="primary" size="icon-lg" class="nav-pill w-full sm:w-11 sm:h-11" title="Conhecer a UniRoad">
                                    <x-heroicon-o-arrow-up-right />
                                </x-btn>
                            </div>
                        </div>
                    </section>

                    <section class="demo-panel">
                        <div
                            class="roadmap-demo flow-container"
                            aria-label="Demo visual do visualizador de roadmaps"
                            x-data="flowCanvas({
                                nodes: [
                                    { id: 'inicio', position: { x: 0, y: 160 }, sourcePosition: 'right', targetPosition: 'left', data: { label: 'Início', kind: 'Início' }, draggable: false },
                                    { id: 'tarefa', position: { x: 480, y: 112 }, sourcePosition: 'right', targetPosition: 'left', data: { label: 'UniRoad em ação', kind: 'Tarefa' }, draggable: false },
                                    { id: 'fim', position: { x: 980, y: 160 }, sourcePosition: 'right', targetPosition: 'left', data: { label: 'Fim', kind: 'Fim' }, draggable: false },
                                ],
                                edges: [
                                    { id: 'inicio-tarefa', source: 'inicio', target: 'tarefa', type: 'bezier' },
                                    { id: 'tarefa-fim', source: 'tarefa', target: 'fim', type: 'bezier' },
                                ],
                                background: 'dots',
                                fitViewOnInit: true,
                                controls: false,
                                pannable: true,
                                panOnDrag: true,
                                noPanClassName: 'af-demo-no-pan-disabled',
                            })"
                            x-init="
                                const wait = (ms) => new Promise((resolve) => setTimeout(resolve, ms));
                                let paused = false;
                                let loopToken = 0;
                                let activeParticle = null;
                                let activeFollow = null;

                                const stopActive = () => {
                                    if (activeParticle && activeParticle.stop) activeParticle.stop();
                                    if (activeFollow && activeFollow.stop) activeFollow.stop();
                                    activeParticle = null;
                                    activeFollow = null;
                                };

                                const fireAndFollow = async (edgeId, token) => {
                                    if (paused || token !== loopToken) return;
                                    activeParticle = $flow.sendParticle(edgeId, {
                                        color: '#22d3c5',
                                        size: 7,
                                        duration: '3s',
                                    });
                                    activeFollow = $flow.follow(activeParticle, {
                                        zoom: 1.65,
                                        padding: 0.18,
                                    });
                                    try {
                                        await activeParticle.finished;
                                    } catch (error) {
                                        return;
                                    }
                                };

                                const runDemo = async (token) => {
                                    await wait(500);
                                    while (!paused && token === loopToken) {
                                        stopActive();
                                        $flow.fitView({ duration: 700, padding: 0.2 });
                                        await wait(900);
                                        if (paused || token !== loopToken) break;
                                        await fireAndFollow('inicio-tarefa', token);
                                        if (paused || token !== loopToken) break;
                                        await fireAndFollow('tarefa-fim', token);
                                        if (paused || token !== loopToken) break;
                                        $flow.fitView({ duration: 900, padding: 0.24 });
                                        await wait(2400);
                                    }
                                };

                                $el.__pauseRoadmapDemo = () => {
                                    paused = true;
                                    loopToken++;
                                    stopActive();
                                };

                                $el.__resumeRoadmapDemo = () => {
                                    if (!paused) return;
                                    paused = false;
                                    loopToken++;
                                    runDemo(loopToken);
                                };

                                runDemo(loopToken);
                            "
                            x-on:pointerdown="$el.__pauseRoadmapDemo && $el.__pauseRoadmapDemo()"
                            x-on:pointerup.window="$el.__resumeRoadmapDemo && $el.__resumeRoadmapDemo()"
                            x-on:pointercancel.window="$el.__resumeRoadmapDemo && $el.__resumeRoadmapDemo()"
                        >
                            <div x-flow-viewport>
                                <template x-for="node in nodes" :key="node.id">
                                    <article
                                        x-flow-node="node"
                                        class="af-landing-node"
                                        :class="{
                                            'is-start': node.id === 'inicio',
                                            'is-task': node.id === 'tarefa',
                                            'is-end': node.id === 'fim'
                                        }"
                                    >
                                        <div>
                                            <div class="demo-label" x-text="node.data.kind"></div>
                                            <h3 x-text="node.data.label"></h3>
                                            <template x-if="node.id === 'tarefa'">
                                                <div>
                                                    <p>A UniRoad organiza turmas, alunos e professores. Professores criam roadmaps estruturados para orientar os alunos durante o aprendizado.</p>
                                                    <div class="demo-actions">
                                                        <span class="demo-action">Organizar turmas</span>
                                                        <span class="demo-action">Guiar alunos</span>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                    </article>
                                </template>
                            </div>
                        </div>
                    </section>
                </main>

                <footer class="mt-10 border-t border-white/10 pt-6 text-sm text-white/60 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <span>© {{ date('Y') }} {{ config('app.name', 'UniRoad') }}.</span>
                    <div class="flex items-center gap-4">
                        <a href="{{ route('sobre') }}" class="hover:text-white">Sobre</a>
                        <a href="{{ route('contato') }}" class="hover:text-white">Contato</a>
                    </div>
                </footer>
            </div>
        </div>

        @ddfsnScripts
    </body>
</html>
