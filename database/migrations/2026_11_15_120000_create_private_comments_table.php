<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('private_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('context');
            $table->text('body');
            $table->timestamps();
            $table->index(['context', 'user_id', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('private_comments');
    }
};
