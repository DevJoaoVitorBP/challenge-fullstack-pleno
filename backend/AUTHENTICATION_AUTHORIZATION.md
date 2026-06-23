# Autenticação e Autorização - Documentação

## 1. Laravel Sanctum (API Tokens) ✅

### Implementação

**Model: User (app/Models/User.php)**
```php
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;
}
```

### Endpoints de Autenticação

#### Register (Público)
```http
POST /api/v1/auth/register
Content-Type: application/json

{
  "name": "João Silva",
  "email": "joao@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}

Response: 201 Created
{
  "success": true,
  "data": {
    "user": { ... },
    "token": "1|AbCdEfGhIjKlMnOpQrStUvWxYz..."
  },
  "message": "Usuário registrado com sucesso"
}
```

#### Login (Público)
```http
POST /api/v1/auth/login
Content-Type: application/json

{
  "email": "joao@example.com",
  "password": "password123"
}

Response: 200 OK
{
  "success": true,
  "data": {
    "user": { ... },
    "token": "1|AbCdEfGhIjKlMnOpQrStUvWxYz..."
  },
  "message": "Autenticação realizada com sucesso"
}
```

#### Get Profile (Protegido)
```http
GET /api/v1/auth/me
Authorization: Bearer <token>

Response: 200 OK
{
  "success": true,
  "data": { ... }
}
```

#### Logout (Protegido)
```http
POST /api/v1/auth/logout
Authorization: Bearer <token>

Response: 200 OK
{
  "success": true,
  "message": "Logout realizado com sucesso"
}
```

---

## 2. Políticas (Policies) ✅

### Implementação

**Arquivo: app/Providers/AuthServiceProvider.php**
```php
protected $policies = [
    Product::class => ProductPolicy::class,
    Category::class => CategoryPolicy::class,
    Order::class => OrderPolicy::class,
];
```

### ProductPolicy (app/Policies/ProductPolicy.php)

| Ação | Permissão | Descrição |
|------|-----------|-----------|
| `viewAny()` | ✅ Qualquer um | Listar produtos (público) |
| `view()` | ✅ Qualquer um | Visualizar um produto (público) |
| `create()` | 👨‍💼 Apenas Admin | Criar novo produto |
| `update()` | 👨‍💼 Apenas Admin | Editar produto |
| `delete()` | 👨‍💼 Apenas Admin | Deletar produto |
| `restore()` | 👨‍💼 Apenas Admin | Restaurar soft-deleted |
| `forceDelete()` | 👨‍💼 Apenas Admin | Deletar permanentemente |

### CategoryPolicy (app/Policies/CategoryPolicy.php)

| Ação | Permissão | Descrição |
|------|-----------|-----------|
| `viewAny()` | ✅ Qualquer um | Listar categorias (público) |
| `view()` | ✅ Qualquer um | Visualizar uma categoria (público) |
| `create()` | 👨‍💼 Apenas Admin | Criar categoria |
| `update()` | 👨‍💼 Apenas Admin | Editar categoria |
| `delete()` | 👨‍💼 Apenas Admin | Deletar categoria |

### OrderPolicy (app/Policies/OrderPolicy.php)

| Ação | Permissão | Descrição |
|------|-----------|-----------|
| `viewAny()` | ✅ Autenticado | Listar próprios pedidos |
| `view()` | 👤 Proprietário ou Admin | Visualizar pedido próprio |
| `create()` | ✅ Autenticado | Criar novo pedido |
| `update()` | 👨‍💼 Apenas Admin | Editar pedido |
| `delete()` | 👨‍💼 Apenas Admin | Deletar pedido |
| `updateStatus()` | 👨‍💼 Apenas Admin | Atualizar status do pedido |

---

## 3. Middleware de Autorização

### AdminMiddleware (app/Http/Middleware/AdminMiddleware.php)

Verifica se o usuário tem a flag `is_admin = true`.

**Uso nas rotas:**
```php
Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::post('/products', [ProductController::class, 'store']);
    Route::put('/products/{id}', [ProductController::class, 'update']);
    Route::delete('/products/{id}', [ProductController::class, 'destroy']);
});
```

**Resposta quando não autorizado (403):**
```json
{
  "success": false,
  "message": "Você não tem permissão para acessar este recurso"
}
```

---

## 4. Rate Limiting (100 requisições/minuto por IP) ✅

### Implementação

**Arquivo: app/Http/Middleware/RateLimitMiddleware.php**

```php
protected const MAX_REQUESTS = 100;  // 100 requisições
protected const TIME_WINDOW = 60;    // por 60 segundos (1 minuto)
```

**Middleware está registrado em:**
- `bootstrap/app.php` - como alias `rate_limit`
- `routes/api.php` - aplicado a todas as rotas da API v1

### Headers de Rate Limit

Todas as respostas incluem:
```http
X-RateLimit-Limit: 100           # Limite total
X-RateLimit-Remaining: 95        # Requisições restantes
X-RateLimit-Reset: 1718879234   # Timestamp de reset (Unix)
Retry-After: 60                  # Segundos até reset (quando limite excedido)
```

### Resposta quando limite excedido (429)

```json
{
  "success": false,
  "message": "Limite de requisições excedido. Máximo de 100 requisições por minuto."
}
```

**Status HTTP:** `429 Too Many Requests`

### Exemplo de Uso

```bash
# Primeiras 100 requisições funcionam
for i in {1..100}; do
  curl -i https://api.example.com/api/v1/products \
    -H "Authorization: Bearer <token>"
done

# Requisição 101 retorna 429
curl -i https://api.example.com/api/v1/products \
  -H "Authorization: Bearer <token>"
# HTTP/1.1 429 Too Many Requests
# X-RateLimit-Remaining: 0
# Retry-After: 60
```

---

## 5. Roles e Permissions

### Implementação Atual

Sistema simples com flag booleano `is_admin`:

```php
// app/Models/User.php
public function isAdmin(): bool
{
    return $this->is_admin;
}
```

**Valores:**
- `is_admin = true` → Administrador (pode criar/editar/deletar)
- `is_admin = false` → Cliente regular (pode visualizar e gerenciar próprios pedidos)

### Criar Usuário Admin

```php
User::create([
    'name' => 'Admin User',
    'email' => 'admin@example.com',
    'password' => Hash::make('password'),
    'is_admin' => true,  // ← Flag de admin
]);
```

### Testar Permissões

```bash
# Como admin (funciona)
curl -X POST https://api.example.com/api/v1/products \
  -H "Authorization: Bearer <admin_token>" \
  -H "Content-Type: application/json" \
  -d '{"name": "Product", ...}'
# 201 Created

# Como usuário regular (falha)
curl -X POST https://api.example.com/api/v1/products \
  -H "Authorization: Bearer <user_token>" \
  -H "Content-Type: application/json" \
  -d '{"name": "Product", ...}'
# 403 Forbidden
# "Você não tem permissão para acessar este recurso"
```

---

## 6. Usando Policies nos Controllers

### Verificação Manual (Atual)

```php
// OrderController@show
if ($order->user_id !== auth()->id() && !auth()->user()->isAdmin()) {
    return $this->forbiddenResponse('Acesso negado');
}
```

### Usando `authorize()` Helper (Otimizado)

```php
public function destroy(int $id)
{
    $product = Product::findOrFail($id);
    
    // Autorizar usando Policy
    $this->authorize('delete', $product);
    
    // Se não autorizado, lança AuthorizationException (403)
    $this->service->deleteProduct($id);
}
```

---

## 7. Fluxos de Autenticação

### Fluxo 1: Usuário Normal criando Pedido

```
1. POST /api/v1/auth/register
   ├─ Cria usuário (is_admin = false)
   └─ Retorna token

2. POST /api/v1/cart/items (com token)
   ├─ Middleware: auth:sanctum ✅
   └─ Carrega itens no carrinho

3. POST /api/v1/orders (com token)
   ├─ Middleware: auth:sanctum ✅
   ├─ Policy: OrderPolicy@create ✅ (qualquer autenticado)
   └─ Cria pedido

4. GET /api/v1/orders/{id} (com token)
   ├─ Middleware: auth:sanctum ✅
   ├─ Policy: OrderPolicy@view ✅ (proprietário)
   └─ Retorna pedido
```

### Fluxo 2: Admin criando Produto

```
1. POST /api/v1/auth/login (admin@example.com)
   ├─ Valida credenciais
   └─ Retorna token com is_admin = true

2. POST /api/v1/products (com token admin)
   ├─ Middleware: auth:sanctum ✅
   ├─ Middleware: admin (verifica is_admin) ✅
   ├─ Policy: ProductPolicy@create ✅
   └─ Cria produto

3. Requisição 101 no mesmo minuto
   ├─ Middleware: rate_limit ❌ (429 Too Many Requests)
   └─ Aguarda reset do limite
```

---

## 8. Testes

Todos os 38 testes passam:
- ✅ Autenticação (register, login, logout, profile)
- ✅ Autorização (admin-only, user-own orders)
- ✅ Proteção de rotas (401, 403)
- ✅ CRUD operations com policies

```bash
php artisan test
# Tests: 38 passed (74 assertions)
```

---

## 9. Checklist de Segurança

- ✅ Autenticação via Sanctum
- ✅ Tokens armazenados de forma segura (hash)
- ✅ Middleware de autenticação em rotas protegidas
- ✅ Políticas para controle granular de permissões
- ✅ Rate limiting por IP (100 req/min)
- ✅ Validação de entrada (Form Requests)
- ✅ Soft deletes em dados sensíveis
- ✅ Autorização em 2 camadas (middleware + policy)

---

## 10. Próximos Passos (Opcional)

1. **Spatie Laravel Permission** - Para roles/permissions avançados
2. **Two-Factor Authentication** - Autenticação de 2 fatores
3. **API Key Management** - Chaves para integrações
4. **Activity Logging** - Registrar ações de admins
5. **Device Trust** - Confiar em dispositivos específicos
