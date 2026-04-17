<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use App\Models\Client;
use App\Models\Sample;
use App\Models\SampleFile;
use App\Models\User;
use App\Policies\ClientPolicy;
use App\Policies\SamplePolicy;
use App\Policies\SampleFilePolicy;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Registra i servizi dell'applicazione.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap dei servizi dell'applicazione.
     */
    public function boot(): void
    {
        // Registrazione esplicita delle Policy
        Gate::policy(Client::class, ClientPolicy::class);
        Gate::policy(Sample::class, SamplePolicy::class);
        Gate::policy(SampleFile::class, SampleFilePolicy::class);

        // Gate personalizzato per il download file.
        // Il metodo 'download' non è un CRUD standard,
        // quindi viene definito esplicitamente qui.
        Gate::define('download', function (User $user, SampleFile $sampleFile) {
            return (new SampleFilePolicy())->download($user, $sampleFile);
        });

        // Contatori sidebar — eseguiti una volta sola per request tramite View::composer.
        // Le variabili sono disponibili automaticamente nel componente sidebar-panel
        // senza toccare i controller.
        View::composer('components.sidebar-panel', function ($view) {
            $view->with([
                'sidebarClientCount' => Client::active()->count(),
                'sidebarSampleCount' => Sample::active()->count(),
                'sidebarStaffCount'  => auth()->check() && auth()->user()->isAdmin()
                                        ? User::count()
                                        : 0,
            ]);
        });
    }
}