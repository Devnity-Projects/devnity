# Configuração Rápida - Active Directory

## ✅ O que já foi feito:

1. ✅ Pacote LdapRecord instalado
2. ✅ Modelo LDAP User criado (`app/Ldap/User.php`)
3. ✅ Migration executada (colunas `domain` e `guid` adicionadas)
4. ✅ Modelo User atualizado para LDAP
5. ✅ Middleware de sincronização de grupos criado
6. ✅ Comando de teste criado
7. ✅ Configuração de autenticação atualizada

## 🔧 Configuração Necessária:

### 1. Configure seu .env com as informações do AD:

```env
# LDAP Configuration
LDAP_LOGGING=true
LDAP_CONNECTION=default
LDAP_HOST=ldap://192.168.1.10
LDAP_PORT=389
LDAP_BASE_DN="DC=empresa,DC=local"
LDAP_USERNAME="CN=ServiceAccount,CN=Users,DC=empresa,DC=local"
LDAP_PASSWORD="senha_da_service_account"
LDAP_USE_SSL=false
LDAP_USE_TLS=false

# Grupos AD (use o DN completo dos grupos)
LDAP_ADMIN_GROUP="CN=Administradores,CN=Users,DC=empresa,DC=local"
LDAP_MANAGER_GROUP="CN=Gerentes,CN=Users,DC=empresa,DC=local"
LDAP_DEVELOPER_GROUP="CN=Desenvolvedores,CN=Users,DC=empresa,DC=local"
LDAP_SUPPORT_GROUP="CN=Suporte,CN=Users,DC=empresa,DC=local"
LDAP_FINANCIAL_GROUP="CN=Financeiro,CN=Users,DC=empresa,DC=local"
```

### 2. Teste a conexão:

```bash
# Testar apenas a conexão
php artisan ldap:test

# Testar e buscar um usuário específico
php artisan ldap:test nome.usuario
```

### 3. Executar o seeder de permissões (se ainda não executou):

```bash
php artisan db:seed --class=RolesAndPermissionsSeeder
```

### 4. Adicionar middleware nas rotas (routes/web.php):

O middleware `sync.ldap.groups` deve ser adicionado nas rotas autenticadas:

```php
Route::middleware(['auth', 'sync.ldap.groups'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    // ... suas outras rotas
});
```

## 📝 Como obter informações do AD:

### Encontrar o DN correto:

**Opção 1: PowerShell no servidor AD**
```powershell
# Buscar usuário
Get-ADUser -Identity "nome.usuario" | Select DistinguishedName

# Buscar grupo
Get-ADGroup -Identity "Desenvolvedores" | Select DistinguishedName

# Listar todos os grupos
Get-ADGroup -Filter * | Select Name, DistinguishedName
```

**Opção 2: ADSI Edit (GUI)**
1. Abrir "ADSI Edit" no Windows Server
2. Conectar ao domínio
3. Navegar até o objeto desejado
4. Clicar direito → Properties
5. Copiar o valor de `distinguishedName`

**Opção 3: Usando o comando criado**
```bash
php artisan ldap:test nome.usuario
```
Isso mostrará o DN do usuário e seus grupos

## 🔄 Fluxo de Autenticação:

1. Usuário acessa `/login`
2. Digita email/username e senha
3. Laravel tenta autenticar no AD via LDAP
4. Se sucesso:
   - Cria/atualiza usuário no banco local
   - Sincroniza nome e email do AD
   - Armazena GUID do AD
5. Middleware `sync.ldap.groups` é executado
6. Busca grupos do usuário no AD
7. Mapeia grupos AD → Roles Laravel
8. Sincroniza roles do usuário
9. Redireciona para dashboard

## 🛠️ Troubleshooting:

### Erro: "Can't contact LDAP server"
- Verificar se o servidor AD está acessível
- Testar com: `telnet seu-servidor-ad.local 389`
- Verificar firewall

### Erro: "Invalid credentials"
- Verificar LDAP_USERNAME (deve ser DN completo)
- Verificar LDAP_PASSWORD
- Testar credenciais manualmente no AD

### Usuário não encontra grupos
- Verificar se o atributo `memberOf` está preenchido no AD
- Alguns usuários podem não ter grupos
- Usar comando: `php artisan ldap:test nome.usuario`

### Roles não sincronizam
- Verificar logs: `tail -f storage/logs/laravel.log`
- Verificar se os DNs dos grupos no .env estão corretos (case-sensitive!)
- Executar seeder de permissões primeiro

## 📊 Logs Úteis:

```bash
# Ver logs em tempo real
tail -f storage/logs/laravel.log | grep -i ldap

# Ver logs de autenticação
tail -f storage/logs/laravel.log | grep -i "auth\|ldap\|login"
```

## 🔐 Segurança:

1. **Service Account**: Crie uma conta específica para LDAP com permissão apenas de leitura
2. **TLS/SSL**: Em produção, sempre use `LDAP_USE_TLS=true` ou `LDAP_USE_SSL=true`
3. **Password**: Nunca commite o .env com senhas reais

## 🧪 Testar Login:

1. Configure o .env com dados corretos
2. Execute: `php artisan ldap:test seu.usuario`
3. Se funcionar, tente fazer login pela interface
4. Verifique os logs se houver erro

## 📞 Próximos Passos:

1. Configure seu .env
2. Teste a conexão
3. Crie os grupos no AD (ou use os existentes)
4. Mapeie os grupos no .env
5. Teste o login

## 💡 Dicas:

- Comece testando com `LDAP_LOGGING=true` para debug
- Use um usuário de teste primeiro
- Verifique se o LDAP_BASE_DN está correto
- O campo email no AD deve estar preenchido
- Grupos podem ter DNs longos - copie exatamente como aparecem

## 🆘 Precisa de ajuda?

Envie o output de:
```bash
php artisan ldap:test seu.usuario
```
