# 🧪 Teste de Login com LDAP

## ✅ Status Atual:

- ✅ Conexão LDAP funcionando
- ✅ Middleware de sincronização instalado
- ✅ Rotas configuradas com `sync.ldap.groups`
- ✅ Sistema de permissões já existente

## 📝 Como Testar o Login:

### Opção 1: Criar usuário no OpenLDAP via phpLDAPadmin

1. **Acesse o phpLDAPadmin:**
   - URL: `http://173.249.1.40:6443` (ou a porta configurada no seu docker-compose)
   - Login DN: `cn=admin,dc=devnity,dc=com,dc=br`
   - Senha: `ESas1dD31pAkJS!FB8V#`

2. **Criar estrutura organizacional (se não existir):**
   ```
   dc=devnity,dc=com,dc=br
   ├── ou=users (Unidade Organizacional para usuários)
   └── ou=groups (Unidade Organizacional para grupos)
   ```

3. **Criar um usuário de teste:**
   - Clique em "Create new entry"
   - Escolha "Generic: User Account"
   - Preencha os campos:
     - **CN (Common Name):** `teste.usuario`
     - **SN (Surname):** `Usuario`
     - **GivenName:** `Teste`
     - **Mail:** `teste@devnity.com.br`
     - **UID:** `teste.usuario`
     - **Password:** `Senha123!`
   - DN final será: `cn=teste.usuario,ou=users,dc=devnity,dc=com,dc=br`

4. **Criar grupos (opcional para testar permissões):**
   - Criar grupo "Desenvolvedores":
     - DN: `cn=Desenvolvedores,ou=groups,dc=devnity,dc=com,dc=br`
     - objectClass: `groupOfNames`
     - member: `cn=teste.usuario,ou=users,dc=devnity,dc=com,dc=br`

### Opção 2: Criar usuário via linha de comando LDIF

Crie um arquivo `usuario-teste.ldif`:

```ldif
# Criar OU users (se não existir)
dn: ou=users,dc=devnity,dc=com,dc=br
objectClass: organizationalUnit
ou: users

# Criar OU groups (se não existir)
dn: ou=groups,dc=devnity,dc=com,dc=br
objectClass: organizationalUnit
ou: groups

# Criar usuário de teste
dn: cn=teste.usuario,ou=users,dc=devnity,dc=com,dc=br
objectClass: inetOrgPerson
objectClass: posixAccount
objectClass: shadowAccount
cn: teste.usuario
sn: Usuario
givenName: Teste
mail: teste@devnity.com.br
uid: teste.usuario
uidNumber: 1001
gidNumber: 1001
homeDirectory: /home/teste.usuario
loginShell: /bin/bash
userPassword: {SSHA}senha_hash_aqui

# Criar grupo Desenvolvedores
dn: cn=Desenvolvedores,ou=groups,dc=devnity,dc=com,dc=br
objectClass: groupOfNames
cn: Desenvolvedores
member: cn=teste.usuario,ou=users,dc=devnity,dc=com,dc=br
```

Execute no servidor:
```bash
ldapadd -x -D "cn=admin,dc=devnity,dc=com,dc=br" -w "ESas1dD31pAkJS!FB8V#" -f usuario-teste.ldif
```

### Opção 3: Usar o usuário admin já existente

O usuário `admin` já existe no LDAP. Para testar:

1. **Buscar o DN exato do admin:**
```bash
php artisan ldap:test admin
```

2. **Se o usuário for encontrado, você pode fazer login com:**
   - Email: `admin@devnity.com.br` (ou o email configurado)
   - Senha: `ESas1dD31pAkJS!FB8V#` (senha do admin LDAP)

## 🔍 Verificar se o usuário existe:

```bash
# Buscar por CN (Common Name)
php artisan ldap:test admin

# Buscar por outro usuário
php artisan ldap:test teste.usuario
```

## 🚀 Testar o Login:

1. **Acesse a aplicação:**
   ```
   https://devnity.test
   ```

2. **Tente fazer login com:**
   - Email: `teste@devnity.com.br` (ou o email do usuário criado)
   - Senha: A senha definida no LDAP

3. **O que deve acontecer:**
   - ✅ Sistema autentica no LDAP
   - ✅ Cria/atualiza usuário no banco local
   - ✅ Sincroniza grupos LDAP → Roles Laravel
   - ✅ Redireciona para `/dashboard`

## 📊 Verificar logs:

```bash
# Ver logs em tempo real
Get-Content storage\logs\laravel.log -Tail 50 -Wait

# Filtrar apenas LDAP
Get-Content storage\logs\laravel.log | Select-String -Pattern "ldap|LDAP"
```

## 🐛 Troubleshooting:

### Erro: "Invalid credentials"
- Verifique se o usuário existe: `php artisan ldap:test nome.usuario`
- Verifique se o email está correto no LDAP
- Tente usar o atributo `uid` ao invés de `mail` para login

### Erro: "User not found"
- Usuário não existe no LDAP
- Base DN incorreto no .env
- Estrutura LDAP diferente (ou=users vs cn=Users)

### Login funciona mas sem permissões
- Verifique se os grupos estão mapeados corretamente no .env
- Os DNs dos grupos devem ser EXATOS (case-sensitive)
- Execute: `php artisan ldap:test nome.usuario` e compare os grupos retornados com os do .env

## 🎯 Próximos Passos:

1. ✅ Conexão LDAP OK
2. ⏳ Criar usuário de teste no LDAP
3. ⏳ Testar login pela interface
4. ⏳ Verificar sincronização de grupos/roles
5. ⏳ Ajustar mapeamento de atributos se necessário

## 📝 Configuração Atual:

```env
LDAP_HOST=173.249.1.40
LDAP_PORT=389
LDAP_BASE_DN="dc=devnity,dc=com,dc=br"
LDAP_USERNAME="cn=admin,dc=devnity,dc=com,dc=br"
```

## 🔐 Atributos LDAP Mapeados:

- `cn` ou `uid` → username (para login)
- `mail` → email
- `cn` → name (nome completo)
- `memberOf` → groups (para sincronização de roles)

## ⚠️ Importante:

- A primeira vez que um usuário LDAP faz login, ele é criado no banco local
- O campo `password` no banco será `null` para usuários LDAP
- O campo `guid` armazena o identificador único do LDAP
- O campo `domain` identifica de qual domínio LDAP o usuário veio

## 💡 Dica Rápida:

Para criar rapidamente um usuário de teste via phpLDAPadmin:

1. Login no phpLDAPadmin
2. Create new entry → Generic: User Account
3. Preencha os campos obrigatórios (CN, SN, Mail, Password)
4. Salve
5. Teste o login na aplicação com o email e senha definidos
