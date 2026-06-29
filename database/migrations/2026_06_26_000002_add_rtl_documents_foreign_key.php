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
        if (!Schema::hasTable('rtl_documents') || !Schema::hasTable('rtls')) {
            return;
        }

        Schema::table('rtl_documents', function (Blueprint $table) {
            $table->foreign('rtl_id')
                ->references('id')
                ->on('rtls')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('rtl_documents')) {
            return;
        }

        Schema::table('rtl_documents', function (Blueprint $table) {
            $table->dropForeign(['rtl_id']);
        });
    }
};
