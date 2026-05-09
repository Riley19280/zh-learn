<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;
use Native\Mobile\Facades\System;

return new class() extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        if (System::isMobile()) {
            Artisan::call('db:seed');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        //
    }
};
