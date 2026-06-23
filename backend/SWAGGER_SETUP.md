# Swagger/OpenAPI Documentation - E-Commerce API

Documentação Swagger foi instalada e configurada! 

## Acesso à Documentação

Após iniciar o servidor, acesse a documentação em:

```
http://localhost:8000/api/documentation
```

## Endpoints Documentados

### Autenticação
- `POST /api/v1/auth/register` - Registrar novo usuário
- `POST /api/v1/auth/login` - Fazer login
- `POST /api/v1/auth/logout` - Fazer logout (requer token)
- `GET /api/v1/auth/me` - Obter dados do usuário (requer token)

### Produtos
- `GET /api/v1/products` - Listar produtos com filtros
- `GET /api/v1/products/{id}` - Obter detalhes de um produto
- `POST /api/v1/products` - Criar produto (admin only)
- `PUT /api/v1/products/{id}` - Atualizar produto (admin only)
- `DELETE /api/v1/products/{id}` - Deletar produto (admin only)

### Categorias
- `GET /api/v1/categories` - Listar categorias com hierarquia
- `GET /api/v1/categories/{id}` - Obter detalhes de categoria
- `GET /api/v1/categories/{id}/products` - Listar produtos da categoria
- `POST /api/v1/categories` - Criar categoria (admin only)
- `PUT /api/v1/categories/{id}` - Atualizar categoria (admin only)
- `DELETE /api/v1/categories/{id}` - Deletar categoria (admin only)

### Carrinho de Compras
- `GET /api/v1/cart` - Obter carrinho do usuário (requer token)
- `POST /api/v1/cart/items` - Adicionar item ao carrinho (requer token)
- `PUT /api/v1/cart/items/{itemId}` - Atualizar quantidade do item (requer token)
- `DELETE /api/v1/cart/items/{itemId}` - Remover item do carrinho (requer token)
- `DELETE /api/v1/cart` - Limpar carrinho (requer token)

### Pedidos
- `GET /api/v1/orders` - Listar pedidos do usuário (requer token)
- `GET /api/v1/orders/{id}` - Obter detalhes do pedido (requer token)
- `POST /api/v1/orders` - Criar pedido (requer token)
- `PUT /api/v1/orders/{id}/status` - Atualizar status do pedido (admin only)

## Autenticação

A API utiliza **Bearer Token** (Sanctum) para autenticação.

Após fazer login, use o token retornado no header:

```
Authorization: Bearer {token}
```

## Como Documentar Novos Endpoints

Adicione anotações OpenAPI nos controllers. Exemplo:

```php
/**
 * @OA\Get(
 *     path="/api/v1/products",
 *     operationId="listProducts",
 *     tags={"Produtos"},
 *     summary="Listar produtos",
 *     description="Retorna uma lista paginada de produtos",
 *     @OA\Response(
 *         response=200,
 *         description="Produtos listados com sucesso"
 *     )
 * )
 */
public function index(): JsonResponse
{
    // ...
}
```

## Regenerar Documentação

Após adicionar ou modificar anotações:

```bash
php artisan l5-swagger:generate
```

## Instalação do L5-Swagger

L5-Swagger foi instalado com:

```bash
composer require darkaonline/l5-swagger
php artisan vendor:publish --provider="L5Swagger\L5SwaggerServiceProvider"
```

A documentação é gerada automaticamente a partir das anotações nos controllers e será servida via Swagger UI.
