<?php

use Stepanenko3\LaravelApiSkeleton\Database\Schema\Blueprint;
use Stepanenko3\LaravelApiSkeleton\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        $this->schema()->create('otp', function (Blueprint $table): void {
            $table->id('id');

            $table->morphs('for');

            $table->string('code', 20);
            $table->string('target');
            $table->string('type');

            $table->boolean('used')->default(false);

            $table->timestamp('expires_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('otp');
    }
};
