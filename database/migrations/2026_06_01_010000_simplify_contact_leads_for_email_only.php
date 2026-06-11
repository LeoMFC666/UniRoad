<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contact_leads', function (Blueprint $table) {
            if (Schema::hasColumn('contact_leads', 'phone')) {
                $table->dropColumn('phone');
            }

            if (Schema::hasColumn('contact_leads', 'contact_type')) {
                $table->dropColumn('contact_type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('contact_leads', function (Blueprint $table) {
            if (! Schema::hasColumn('contact_leads', 'contact_type')) {
                $table->string('contact_type', 20)->default('email')->after('id');
            }

            if (! Schema::hasColumn('contact_leads', 'phone')) {
                $table->text('phone')->nullable()->after('email');
            }
        });
    }
};
