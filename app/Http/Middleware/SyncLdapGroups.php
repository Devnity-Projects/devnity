<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use LdapRecord\Container;
use Illuminate\Support\Facades\Log;

class SyncLdapGroups
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        // Verificar se é usuário autenticado via LDAP
        if ($user && $user->guid) {
            try {
                // Buscar usuário no LDAP usando GUID
                $ldapUser = \App\Ldap\User::findByGuid($user->guid);

                if ($ldapUser) {
                    // Obter grupos do usuário no AD
                    $groups = $ldapUser->getGroups();
                    
                    // Sincronizar roles baseado nos grupos
                    $user->syncRolesFromLdap($groups);
                    
                    Log::info("Grupos LDAP sincronizados para {$user->email}", [
                        'groups_count' => count($groups),
                        'roles' => $user->getRoleNames()->toArray()
                    ]);
                } else {
                    Log::warning("Usuário LDAP não encontrado no AD: {$user->email}");
                }
            } catch (\Exception $e) {
                Log::error('Erro ao sincronizar grupos LDAP', [
                    'user' => $user->email,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }

        return $next($request);
    }
}
