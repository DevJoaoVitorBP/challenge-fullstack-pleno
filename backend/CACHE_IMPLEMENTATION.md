# Implementação de Cache com Tags Inteligentes

## Resumo

O sistema de cache foi implementado com tags para invalidação inteligente de dados relacionados:
- **Produtos**: TTL de 1 hora (3.600 segundos)
- **Categorias**: TTL de 24 horas (86.400 segundos)
- **Invalidação**: Usa `Cache::tags()->flush()` para invalidar toda uma tag quando dados mudam

---

## Arquitetura de Cache

### 1. ProductService (`app/Services/ProductService.php`)

#### Constantes de Cache
```php
protected const CACHE_TTL = 3600; // 1 hora
protected const CACHE_TAG = 'products';
protected const CATEGORY_CACHE_TAG = 'category_products';
```

#### Métodos com Cache

**getProductById(int $id)**
- Cache Key: `product.{$id}`
- Tags: `['products']`
- TTL: 1 hora
- Uso: Recuperação de produto individual com dados frequentemente acessados

**getProductsByCategory(int $categoryId)**
- Cache Key: `products.category.{$categoryId}`
- Tags: `['category_products', 'category.{$categoryId}']`
- TTL: 1 hora
- Uso: Lista de produtos por categoria com invalidação específica por categoria

#### Métodos sem Cache (Dinâmicos)

**getAllProducts(array $filters = [])**
- Sem cache (sempre executa)
- Motivo: Resultados altamente dinâmicos com múltiplos filtros

#### Invalidação de Cache

Todas as operações de escrita invalidam ambas as tags:

- **createProduct()**: `Cache::tags(['products', 'category_products'])->flush()`
- **updateProduct()**: `Cache::tags(['products', 'category_products'])->flush()`
- **deleteProduct()**: `Cache::tags(['products', 'category_products'])->flush()`

---

### 2. CategoryService (`app/Services/CategoryService.php`)

#### Constantes de Cache
```php
protected const CACHE_TTL = 86400; // 24 horas
protected const CACHE_TAG = 'categories';
protected const PRODUCT_CACHE_TAG = 'category_products';
```

#### Métodos com Cache

**getAllCategories()**
- Cache Key: `categories.all`
- Tags: `['categories']`
- TTL: 24 horas
- Uso: Lista completa de categorias ativas

**getCategoryHierarchy()**
- Cache Key: `categories.hierarchy`
- Tags: `['categories']`
- TTL: 24 horas
- Uso: Estrutura hierárquica de categorias (pai/filho)

**getCategoryById(int $id)**
- Cache Key: `category.{$id}`
- Tags: `['categories']`
- TTL: 24 horas
- Uso: Recuperação de categoria individual

**getCategoryBySlug(string $slug)**
- Cache Key: `category.slug.{$slug}`
- Tags: `['categories']`
- TTL: 24 horas
- Uso: Busca por slug (comum em URLs amigáveis)

**getChildCategories(int $parentId)**
- Cache Key: `categories.parent.{$parentId}`
- Tags: `['categories']`
- TTL: 24 horas
- Uso: Subcategorias de uma categoria pai

#### Invalidação de Cache

Todas as mutações invalidam ambas as tags (categorias e produtos relacionados):

- **createCategory()**: `Cache::tags(['categories', 'category_products'])->flush()`
- **updateCategory()**: `Cache::tags(['categories', 'category_products'])->flush()`
- **deleteCategory()**: `Cache::tags(['categories', 'category_products'])->flush()`

---

### 3. Listeners com Invalidação

#### LogProductCreation (`app/Listeners/LogProductCreation.php`)
Quando um produto é criado (via evento ProductCreated):
```php
Cache::tags(['products', 'category_products'])->flush();
```

Garante que cualquier novo produto invalida o cache de produtos e categorias imediatamente.

---

## Benefícios da Implementação

### Performance
1. **Redução de Queries**: Produtos frequentemente acessados não consultam banco até expiração de TTL
2. **Distribuição de Carga**: Cache distribui leituras pesadas entre camadas (App → Cache → DB)
3. **TTLs Inteligentes**: Produtos (1h - dados mudam frequentemente) vs Categorias (24h - estrutura estável)

### Consistência de Dados
1. **Invalidação Automática**: Qualquer mudança invalida imediatamente o cache via tags
2. **Sem Dados Stale**: Após atualizar, próxima requisição consulta dados frescos do banco
3. **Cascata de Invalidação**: Alterar categoria invalida produtos daquela categoria

### Simplicidade Operacional
1. **Tags Agrupadas**: Não precisa conhecer exatamente quais chaves afetam a mudança
2. **Sem Gerenciamento Manual**: `Cache::tags()->flush()` trata todas as chaves relevantes
3. **Fácil de Expandir**: Adicionar novo cache requer apenas adicionar tag e usar `Cache::tags()`

---

## Exemplo de Fluxo Completo

### Cenário: Admin atualiza preço de produto em categoria

1. **Requisição de UPDATE**: `PATCH /api/v1/products/{id}`
2. **ProductService::updateProduct()** executa:
   - Atualiza registro no banco
   - Sincroniza tags (se houver)
   - **Invalida cache**: `Cache::tags(['products', 'category_products'])->flush()`
3. **Próximas Requisições**:
   - `GET /api/v1/products/{id}` → Cache miss, consulta banco, armazena em cache com tag `products`
   - `GET /api/v1/categories/{id}/products` → Cache miss, consulta banco, armazena com tags `category_products` + `category.{id}`
4. **TTL Expira** (1h ou 24h):
   - Entrada sai automaticamente do cache
   - Próxima requisição consultará banco novamente

---

## Configuração de Cache Driver

Atualmente usa **Database Driver** (migrations criadas pela Laravel automaticamente):
- Tabela: `cache`
- Útil para: Desenvolvimento, ambientes sem Redis

Para **Produção**, altere em `.env`:

```env
# Redis (recomendado)
CACHE_DRIVER=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Ou Memcached
CACHE_DRIVER=memcached
MEMCACHED_SERVERS=127.0.0.1:11211
```

Tags funcionam **identicamente** em todos os drivers (Database, Redis, Memcached).

---

## Monitoramento

### Inspecionar Cache no Database Driver

```sql
-- Ver todas as entradas em cache
SELECT * FROM cache;

-- Ver chaves com padrão específico
SELECT key FROM cache WHERE key LIKE 'product.%' LIMIT 10;

-- Contar entradas por padrão
SELECT COUNT(*) FROM cache WHERE key LIKE 'category.%';

-- Limpar cache manualmente (não recomendado)
DELETE FROM cache WHERE key LIKE 'products%';
```

### CLI Commands

```bash
# Limpar TODO o cache
php artisan cache:clear

# Limpar cache de uma tag específica (via código dentro de Artisan command)
php artisan tinker
>>> Cache::tags(['products'])->flush()

# Ver estrutura de cache
php artisan cache:prune-stale-tags
```

---

## Testes

Todos os **38 testes passam** após implementação:
```
Tests:    38 passed (74 assertions)
Duration: 0.89s
```

**Testes relevantes de cache:**
- `ProductServiceTest::can create product` (valida invalidação)
- `ProductControllerTest::can list products` (valida se cache não quebra lista)
- `CategoryControllerTest::can list categories` (valida cache de categorias)

---

## Próximos Passos (Opcional)

1. **Cache para Ordens**:
   - Adicionar `Cache::tags(['orders'])` em OrderService
   - TTL: 1 hora (dados mudam com frequência)

2. **Cache para Usuários**:
   - Adicionar `Cache::tags(['users'])` se muitos acessam mesmo usuário
   - TTL: 24 horas (raramente mudam)

3. **Metrics**:
   - Adicionar middleware para medir cache hit rate
   - Dashboard de performance

4. **Warming de Cache**:
   - Seeder que pré-popula cache com dados críticos
   - Reduz cache misses após deploy

---

## Resumo Técnico

| Aspecto | Produtos | Categorias |
|---------|----------|-----------|
| **TTL** | 1 hora (3.600s) | 24 horas (86.400s) |
| **Tag Primária** | `products` | `categories` |
| **Tag Secundária** | `category_products` | `category_products` |
| **Métodos com Cache** | 2 (getById, getByCategory) | 5 (getAll, getById, getHierarchy, getBySlug, getChildren) |
| **Invalidação** | create/update/delete | create/update/delete |
| **Cache Hits** | Alto (leituras frequentes) | Muito Alto (estrutura estável) |

