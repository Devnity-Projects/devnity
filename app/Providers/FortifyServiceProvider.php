<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Actions\RedirectIfTwoFactorAuthenticatable;
use Laravel\Fortify\Fortify;
use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Authenticated;

class FortifyServiceProvider extends ServiceProvider
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
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);
        Fortify::redirectUserForTwoFactorAuthenticationUsing(RedirectIfTwoFactorAuthenticatable::class);

        // Customizar autenticação para aceitar username ou email
        Fortify::authenticateUsing(function (Request $request) {
            $credentials = [
                'password' => $request->password,
            ];

            // Detectar se é email ou username
            // OpenLDAP usa 'uid', Active Directory usa 'samaccountname'
            $loginField = filter_var($request->email, FILTER_VALIDATE_EMAIL) ? 'email' : 'uid';
            $credentials[$loginField] = $request->email;

            if (\Illuminate\Support\Facades\Auth::attempt($credentials, $request->filled('remember'))) {
                return \Illuminate\Support\Facades\Auth::user();
            }

            return null;
        });

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });

            // Adicione as views SPA:
        Fortify::loginView(fn () => inertia('auth/Login'));
        Fortify::registerView(fn () => inertia('auth/Register'));
        Fortify::requestPasswordResetLinkView(fn () => inertia('auth/ForgotPassword'));
        Fortify::resetPasswordView(fn ($request) => inertia('auth/ResetPassword', [
            'request' => $request
        ]));

        // Sincronizar grupos LDAP após autenticação
        Event::listen(Authenticated::class, function (Authenticated $event) {
            $user = $event->user;
            
            // Verificar se é usuário LDAP (tem guid) e é instância do modelo User
            if ($user instanceof \App\Models\User && !empty($user->guid)) {
                try {
                    $ldapUser = \App\Ldap\User::findByGuid($user->guid);
                    
                    if ($ldapUser) {
                        $groups = $ldapUser->getGroups() ?? [];
                        $user->syncRolesFromLdap($groups);
                        
                        \Log::info('Grupos LDAP sincronizados no login', [
                            'user' => $user->email ?? $user->samaccountname,
                            'groups_count' => count($groups),
                            'roles' => $user->getRoleNames()->toArray(),
                        ]);
                    }
                } catch (\Exception $e) {
                    \Log::error('Erro ao sincronizar grupos LDAP no login', [
                        'user_id' => $user->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        });
    }
}
