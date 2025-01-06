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
        Schema::create('courses', static function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('category_id');
            $table->unsignedBigInteger('grade_level_id');
            $table->string('name');
            $table->text('description');
            $table->double('price', 8, 2)->default(0);
            $table->string('cover_image');
            $table->time('course_duration')->default('00:00:00');
            $table->boolean('is_enroll')->default(false);
            $table->timestamps();
            $table->softDeletes();
            $table->enum('status', ['active', 'inactive'])->default('inactive');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
