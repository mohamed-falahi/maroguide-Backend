<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('cities', function (Blueprint $table) {
            $table->string('image')->nullable()->after('region');
        });
    }

    public function down()
    {
        Schema::table('cities', function (Blueprint $table) {
            $table->dropColumn('image');
        });
    }
};
