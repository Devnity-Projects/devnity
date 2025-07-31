# CRUD de Clientes - DevNity

## Visão Geral

O sistema de gerenciamento de clientes do DevNity oferece funcionalidades completas de CRUD (Create, Read, Update, Delete) com recursos avançados para uma gestão eficiente da carteira de clientes.

## 🚀 Funcionalidades Implementadas

### ✅ Operações CRUD Básicas
- **Create**: Cadastro de novos clientes (Pessoa Física ou Jurídica)
- **Read**: Visualização detalhada e listagem com filtros
- **Update**: Edição de dados dos clientes
- **Delete**: Remoção individual ou em lote

### ✅ Validações Avançadas
- **Validação de CPF/CNPJ**: Algoritmo de validação em tempo real
- **Validação de E-mail**: Verificação de formato e DNS
- **Validação de Telefone**: Formatação automática
- **Validação de CEP**: Integração com API ViaCEP

### ✅ Formatação Automática
- **Documentos**: CPF (000.000.000-00) e CNPJ (00.000.000/0000-00)
- **Telefones**: (00) 0000-0000 e (00) 90000-0000
- **CEP**: 00000-000

### ✅ Funcionalidades de Busca
- **Busca textual**: Nome, e-mail, documento, responsável
- **Filtros**: Status (ativo/inativo), tipo (PF/PJ)
- **Ordenação**: Por nome, e-mail, data de cadastro, status

### ✅ Ações em Lote (Bulk Actions)
- Ativação/desativação múltipla
- Exclusão múltipla
- Seleção de todos os itens

### ✅ Exportação de Dados
- Exportação em CSV com filtros aplicados
- Codificação UTF-8 com BOM
- Separador de campos configurável

### ✅ Integração com CEP
- Busca automática de endereço via CEP
- Preenchimento automático dos campos
- Validação de CEP brasileiro

## 🎯 Estrutura do Sistema

### Backend (Laravel)

#### Modelo (`app/Models/Client.php`)
```php
// Constantes
TYPE_INDIVIDUAL = 'Pessoa Física'
TYPE_LEGAL = 'Pessoa Jurídica'
STATUS_ACTIVE = 'ativo'
STATUS_INACTIVE = 'inativo'

// Relacionamentos
projects() // HasMany

// Scopes
active() // Clientes ativos
inactive() // Clientes inativos
byType($type) // Por tipo
search($term) // Busca textual
byStatus($status) // Por status

// Accessors
formatted_document // Documento formatado
formatted_phone // Telefone formatado
formatted_zip_code // CEP formatado
full_address // Endereço completo

// Métodos
isActive() // Verifica se está ativo
isLegalPerson() // Verifica se é PJ
isIndividual() // Verifica se é PF
validateDocument($doc) // Valida CPF/CNPJ
```

#### Controller (`app/Http/Controllers/ClientController.php`)
```php
index() // Listagem com filtros e paginação
create() // Formulário de criação
store() // Salvar novo cliente
show() // Visualizar cliente
edit() // Formulário de edição
update() // Atualizar cliente
destroy() // Remover cliente
export() // Exportar CSV
toggleStatus() // Alternar status
bulkDestroy() // Remoção em lote
bulkToggleStatus() // Alteração de status em lote
```

#### Validação (`app/Http/Requests/ClientRequest.php`)
- Validação de campos obrigatórios
- Validação de documentos únicos
- Validação de formato de CPF/CNPJ
- Sanitização automática de dados

#### API CEP (`app/Http/Controllers/CepController.php`)
```php
search($cep) // Busca informações do CEP
```

### Frontend (Vue.js)

#### Páginas
- **Index**: Listagem com filtros, busca e ações em lote
- **Create**: Formulário de criação com validação
- **Edit**: Formulário de edição
- **Show**: Visualização detalhada com projetos relacionados

#### Componentes Modulares
- **BasicInfoSectionEnhanced**: Dados principais com validação
- **ContactInfoSection**: Informações de contato
- **ResponsibleInfoSection**: Dados do responsável
- **RegistrationSection**: Inscrições (PJ)
- **AddressSection**: Endereço com busca por CEP
- **NotesSection**: Observações
- **BulkActions**: Ações em lote

## 🛠️ Como Usar

### 1. Acessar Clientes
```
GET /clients
```

### 2. Criar Novo Cliente
```
GET /clients/create
POST /clients
```

### 3. Visualizar Cliente
```
GET /clients/{id}
```

### 4. Editar Cliente
```
GET /clients/{id}/edit
PUT /clients/{id}
```

### 5. Remover Cliente
```
DELETE /clients/{id}
```

### 6. Ações Especiais

#### Alternar Status
```
POST /clients/{id}/toggle-status
```

#### Exportar CSV
```
GET /clients-export?search=&status=&type=
```

#### Buscar CEP
```
GET /cep/{cep}
```

#### Ações em Lote
```
DELETE /clients/bulk-destroy
PATCH /clients/bulk-toggle-status
```

## 📊 Estatísticas Disponíveis

- Total de clientes
- Clientes ativos/inativos
- Pessoas físicas/jurídicas
- Clientes por estado
- Clientes cadastrados por período

## 🧪 Testando o Sistema

### Executar Testes Automatizados
```bash
php artisan test:clients-crud
```

### Popular Base com Dados de Teste
```bash
php artisan db:seed --class=EnhancedClientSeeder
```

## 🔧 Configurações

### Validações Customizáveis
```php
// ClientRequest.php
'document' => [
    'required',
    'string',
    'max:20',
    Rule::unique('clients', 'document')->ignore($clientId),
    function ($attribute, $value, $fail) {
        if (!Client::validateDocument($value)) {
            $fail('O documento informado não é válido.');
        }
    }
]
```

### API Externa (CEP)
```php
// CepController.php
$response = Http::timeout(10)
    ->get("https://viacep.com.br/ws/{$cep}/json/");
```

## 🎨 Interface do Usuário

### Características
- Design responsivo
- Tema escuro/claro
- Animações suaves
- Feedback visual
- Carregamento assíncrono
- Validação em tempo real

### Tecnologias Frontend
- Vue.js 3 (Composition API)
- Inertia.js
- Tailwind CSS
- Lucide Icons
- TypeScript

## 📈 Performance

### Otimizações Implementadas
- Paginação de resultados
- Carregamento lazy de dados
- Cache de consultas frequentes
- Índices de banco otimizados
- Debounce em buscas

### Índices de Banco
```sql
-- Migration: clients table
$table->index(['status', 'type']);
$table->index(['created_at']);
$table->unique(['document']);
$table->unique(['email']);
```

## 🔒 Segurança

### Medidas Implementadas
- Validação server-side
- Sanitização de inputs
- Rate limiting em APIs
- CSRF protection
- Mass assignment protection
- SQL injection prevention

## 📝 Logs e Auditoria

### Eventos Registrados
- Criação de clientes
- Atualizações de dados
- Mudanças de status
- Exclusões
- Tentativas de acesso

## 🚀 Próximas Melhorias

### Planejadas
- [ ] Importação em lote via CSV/Excel
- [ ] Histórico de alterações
- [ ] Integração com WhatsApp Business
- [ ] Geolocalização de clientes
- [ ] Dashboard de analytics
- [ ] API REST completa
- [ ] Notificações automáticas
- [ ] Backup automático

## 📚 Documentação Adicional

- [Documentação da API](api-docs.md)
- [Guia de Contribuição](contributing.md)
- [Troubleshooting](troubleshooting.md)

---

*Desenvolvido com ❤️ pela equipe DevNity*
