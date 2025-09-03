# Sistema Financeiro - DevNity

## 📊 Visão Geral

O sistema financeiro do DevNity é uma solução completa para gestão de receitas e despesas, oferecendo controle total sobre as finanças da empresa com funcionalidades avançadas de categorização, relatórios e análises.

## 🚀 Funcionalidades Implementadas

### 💰 Dashboard Financeiro
- **Visão Geral**: Métricas principais (receitas, despesas, lucro líquido)
- **Comparações**: Crescimento vs período anterior
- **Alertas**: Transações vencidas e próximos vencimentos
- **Gráficos**: Evolução financeira por período
- **Exportação**: Relatórios em CSV

### 🏷️ Categorias Financeiras
- **Tipos**: Receitas e Despesas
- **Personalização**: Cores e ícones customizáveis
- **Organização**: Sistema de categorização flexível
- **Status**: Ativação/desativação de categorias
- **Estatísticas**: Total de transações por categoria

### 💳 Transações Financeiras
- **CRUD Completo**: Criar, visualizar, editar e excluir
- **Tipos**: Receitas e despesas
- **Status**: Pendente, pago, vencido, cancelado
- **Recorrência**: Única, mensal, trimestral, semestral, anual
- **Parcelamento**: Suporte a múltiplas parcelas
- **Vinculações**: Clientes e projetos
- **Métodos de Pagamento**: PIX, cartão, boleto, etc.

### 📈 Relatórios e Análises
- **Filtros Avançados**: Por período, categoria, status, cliente
- **Busca Textual**: Localização rápida de transações
- **Ordenação**: Múltiplos critérios de organização
- **Paginação**: Performance otimizada para grandes volumes

### ⚡ Ações em Lote
- **Seleção Múltipla**: Checkbox para seleção em massa
- **Mudança de Status**: Marcar como pago/pendente
- **Exclusão**: Remoção em lote
- **Ativação/Desativação**: Para categorias

## 🏗️ Estrutura Técnica

### Models

#### FinancialCategory
```php
// Campos principais
- name: string (nome da categoria)
- description: string (descrição)
- type: enum (income|expense)
- color: string (cor hexadecimal)
- icon: string (nome do ícone)
- is_active: boolean

// Relacionamentos
- transactions(): HasMany

// Métodos principais
- isIncome(): bool
- isExpense(): bool
- getTotalTransactions(): float
```

#### FinancialTransaction
```php
// Campos principais
- title: string
- description: text
- type: enum (income|expense)
- amount: decimal(12,2)
- due_date: date
- payment_date: date (nullable)
- status: enum (pending|paid|overdue|cancelled)
- recurrence: enum (none|monthly|quarterly|biannual|yearly)
- installments: integer
- current_installment: integer
- payment_method: string
- notes: text

// Relacionamentos
- category(): BelongsTo
- client(): BelongsTo
- project(): BelongsTo

// Métodos principais
- markAsPaid(): void
- cancel(): void
- isOverdue(): bool
- getDaysUntilDue(): int
```

#### FinancialBudget
```php
// Campos principais
- name: string
- description: text
- amount: decimal(12,2)
- spent: decimal(12,2)
- start_date: date
- end_date: date
- period: enum
- status: enum

// Métodos principais
- updateSpentAmount(): void
- shouldAlert90Percent(): bool
- shouldAlert100Percent(): bool
```

### Controllers

#### FinancialDashboardController
- **index()**: Dashboard principal com métricas
- **export()**: Exportação de relatórios
- **calculateFinancialStats()**: Cálculo de estatísticas
- **getChartData()**: Dados para gráficos
- **getBudgetAlerts()**: Alertas de orçamento

#### FinancialTransactionController
- **CRUD Completo**: index, create, store, show, edit, update, destroy
- **Ações Especiais**: markAsPaid, markAsPending, cancel
- **Bulk Actions**: bulkDestroy, bulkUpdateStatus
- **Parcelamento**: createInstallments()

#### FinancialCategoryController
- **CRUD Completo**: Gestão de categorias
- **Status**: toggleStatus()
- **Bulk Actions**: bulkDestroy, bulkToggleStatus

### Requests & Resources

#### FinancialTransactionRequest
```php
// Validações principais
- title: required|string|max:255
- amount: required|numeric|min:0.01|max:999999999.99
- due_date: required|date
- category_id: required|exists:financial_categories,id
- installments: required|integer|min:1|max:360
```

#### FinancialCategoryRequest
```php
// Validações principais
- name: required|string|max:255|unique
- type: required|in:income,expense
- color: required|regex:/^#[a-fA-F0-9]{6}$/
```

## 🎨 Interface do Usuário

### Componentes Vue.js

#### Dashboard Financeiro
- **Cards de Métricas**: Receitas, despesas, lucro líquido
- **Gráficos**: Evolução temporal das finanças
- **Listas**: Transações recentes, vencidas e próximas
- **Alertas**: Orçamentos excedidos
- **Ações Rápidas**: Links para principais funcionalidades

#### Lista de Transações
- **Tabela Responsiva**: Design adaptável
- **Filtros Avançados**: Busca e filtros múltiplos
- **Ações Inline**: Visualizar, editar, excluir
- **Status Visual**: Cores indicativas por status
- **Paginação**: Navegação otimizada

#### Formulário de Transação
- **Seleção de Tipo**: Interface intuitiva receita/despesa
- **Campos Dinâmicos**: Categorias filtradas por tipo
- **Validação**: Feedback em tempo real
- **Parcelamento**: Preview de parcelas
- **Vinculações**: Seleção de clientes e projetos

#### Gestão de Categorias
- **Grid Layout**: Visualização em cards
- **Personalização**: Seletor de cores e ícones
- **Estatísticas**: Contadores e totais
- **Ações Rápidas**: Toggle status e edição

## 📋 Rotas Implementadas

### Dashboard
```php
GET /financial - Dashboard principal
GET /financial/export - Exportação de relatórios
```

### Categorias
```php
GET /financial/categories - Listar categorias
GET /financial/categories/create - Formulário de criação
POST /financial/categories - Salvar categoria
GET /financial/categories/{id} - Visualizar categoria
GET /financial/categories/{id}/edit - Formulário de edição
PUT /financial/categories/{id} - Atualizar categoria
DELETE /financial/categories/{id} - Excluir categoria
POST /financial/categories/{id}/toggle-status - Alternar status
DELETE /financial/categories/bulk-destroy - Exclusão em lote
PATCH /financial/categories/bulk-toggle-status - Status em lote
```

### Transações
```php
GET /financial/transactions - Listar transações
GET /financial/transactions/create - Formulário de criação
POST /financial/transactions - Salvar transação
GET /financial/transactions/{id} - Visualizar transação
GET /financial/transactions/{id}/edit - Formulário de edição
PUT /financial/transactions/{id} - Atualizar transação
DELETE /financial/transactions/{id} - Excluir transação
PATCH /financial/transactions/{id}/mark-as-paid - Marcar como pago
PATCH /financial/transactions/{id}/mark-as-pending - Marcar como pendente
PATCH /financial/transactions/{id}/cancel - Cancelar transação
DELETE /financial/transactions/bulk-destroy - Exclusão em lote
PATCH /financial/transactions/bulk-update-status - Status em lote
```

## 🎯 Categorias Padrão

### Receitas
- **Desenvolvimento de Software**: Projetos de desenvolvimento
- **Consultoria**: Serviços de consultoria técnica
- **Manutenção**: Manutenção de sistemas existentes
- **Licenças de Software**: Licenciamento de produtos

### Despesas
- **Infraestrutura**: Servidores, hospedagem, cloud
- **Ferramentas e Software**: Licenças de desenvolvimento
- **Marketing e Publicidade**: Investimentos em marketing
- **Educação e Treinamento**: Cursos e certificações
- **Equipamentos**: Hardware e periféricos
- **Impostos e Taxas**: Obrigações tributárias
- **Escritório**: Aluguel, utilidades, internet
- **Terceirizados**: Freelancers e prestadores

## 📊 Dados de Exemplo

O sistema inclui um seeder com dados realistas:
- **13+ transações de exemplo**
- **Diversos tipos de receitas e despesas**
- **Transações com parcelas**
- **Diferentes status e métodos de pagamento**
- **Vinculações com clientes e projetos**

## 🔒 Segurança e Validação

### Validações Backend
- **Valores Monetários**: Formatação e limitação
- **Datas**: Validação de períodos
- **Relacionamentos**: Verificação de existência
- **Permissões**: Autorização por usuário

### Validações Frontend
- **Formulários**: Validação em tempo real
- **Formatação**: Máscaras de moeda e data
- **Feedback**: Mensagens de erro claras
- **UX**: Prevenção de ações destrutivas

## 🚀 Próximas Funcionalidades

### Planejadas
- **Orçamentos**: Sistema de orçamentos por categoria
- **Relatórios Avançados**: Gráficos e análises detalhadas
- **Conciliação Bancária**: Importação de extratos
- **API de Integração**: Webhooks e integrações
- **Notificações**: Alertas automáticos
- **Backup**: Exportação completa de dados
- **Multi-moeda**: Suporte a múltiplas moedas
- **Fluxo de Aprovação**: Workflow de aprovações

### Melhorias
- **Performance**: Otimizações de consulta
- **Mobile**: App nativo
- **Offline**: Sincronização offline
- **IA**: Categorização automática
- **Previsões**: Análise preditiva

## 💡 Como Usar

### 1. Configurar Categorias
1. Acesse **Financeiro > Categorias**
2. Crie categorias para receitas e despesas
3. Personalize cores e ícones
4. Ative/desative conforme necessário

### 2. Registrar Transações
1. Acesse **Financeiro > Transações**
2. Clique em **Nova Transação**
3. Selecione tipo (receita/despesa)
4. Preencha informações básicas
5. Configure parcelamento se necessário
6. Vincule a clientes/projetos
7. Salve a transação

### 3. Acompanhar Métricas
1. Acesse **Dashboard Financeiro**
2. Visualize métricas principais
3. Monitore transações vencidas
4. Analise crescimento mensal
5. Exporte relatórios quando necessário

### 4. Gerenciar Status
- **Marcar como Pago**: Quando receber/pagar
- **Acompanhar Vencidos**: Lista de pendências
- **Cancelar**: Para transações não realizadas
- **Parcelar**: Para facilitar fluxo de caixa

## 🎨 Design System

O sistema financeiro segue o design system do DevNity:
- **Cores**: Verde (receitas), vermelho (despesas), azul (neutro)
- **Tipografia**: Consistente com o sistema
- **Componentes**: Reutilizáveis e modulares
- **Responsividade**: Mobile-first
- **Acessibilidade**: WCAG 2.1 AA

---

**Desenvolvido com ❤️ pela equipe DevNity**
