<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $hasRoadmapNodeId = Schema::hasColumn('atividades', 'roadmap_node_id');
        $hasActivityAttachments = Schema::hasColumn('atividades', 'anexos');

        Schema::table('atividades', function (Blueprint $table) use ($hasRoadmapNodeId, $hasActivityAttachments) {
            if (! $hasRoadmapNodeId) {
                $table->string('roadmap_node_id')->nullable()->after('roadmap_id')->index();
            }

            if (! $hasActivityAttachments) {
                $table->json('anexos')->nullable()->after('descricao');
            }
        });

        $hasSubmissionFiles = Schema::hasColumn('submissoes', 'arquivos');

        Schema::table('submissoes', function (Blueprint $table) use ($hasSubmissionFiles) {
            if (! $hasSubmissionFiles) {
                $table->json('arquivos')->nullable()->after('arquivo');
            }
        });
    }

    public function down(): void
    {
        Schema::table('submissoes', function (Blueprint $table) {
            if (Schema::hasColumn('submissoes', 'arquivos')) {
                $table->dropColumn('arquivos');
            }
        });

        Schema::table('atividades', function (Blueprint $table) {
            if (Schema::hasColumn('atividades', 'anexos')) {
                $table->dropColumn('anexos');
            }

            if (Schema::hasColumn('atividades', 'roadmap_node_id')) {
                $table->dropIndex(['roadmap_node_id']);
                $table->dropColumn('roadmap_node_id');
            }
        });
    }
};
