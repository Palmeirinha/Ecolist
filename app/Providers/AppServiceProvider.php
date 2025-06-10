<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Model;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);
        
        Model::preventLazyLoading(!app()->isProduction());
        
        if (!Schema::hasTable('alimentos')) {
            return;
        }

        if (!Schema::hasIndex('alimentos', 'alimentos_user_id_index')) {
            Schema::table('alimentos', function ($table) {
                $table->index('user_id');
                $table->index('categoria_id');
                $table->index('validade');
            });
        }
    }
}
