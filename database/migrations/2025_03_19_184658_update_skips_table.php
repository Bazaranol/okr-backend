<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('skips', function (Blueprint $table) {
            $table->dropColumn('document_path');
            $table->json('document_paths')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('skips', function (Blueprint $table) {
            $table->string('document_path')->nullable();
            $table->dropColumn('document_paths');
        });
    }
};
