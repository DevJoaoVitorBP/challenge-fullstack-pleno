# 🚀 CI/CD Pipeline - E-Commerce API

## Visão Geral

Este projeto possui um pipeline CI/CD completo implementado com **GitHub Actions**, garantindo:

- ✅ Testes automatizados (PHPUnit)
- ✅ Qualidade de código (PSR-12 com Pint)
- ✅ Documentação automática (Swagger/OpenAPI)
- ✅ Coverage de testes
- ✅ Suporte a múltiplas versões PHP (8.2, 8.3)

## 📁 Estrutura do Pipeline

```
.github/
└── workflows/
    └── ci.yml          # Pipeline principal
.env.testing           # Configuração para testes
phpunit.xml           # Configuração PHPUnit
```

## 🔄 Pipeline GitHub Actions

### Arquivo: `.github/workflows/ci.yml`

O pipeline é acionado em:

```yaml
on:
  push:
    branches: [ main, master, develop ]
  pull_request:
    branches: [ main, master, develop ]
```

### Etapas do Pipeline

#### 1️⃣ **Job: test** (Testes Automatizados)

**Configuração:**
- Runs-on: `ubuntu-latest`
- Matriz PHP: `8.2`, `8.3`
- Extensões: pdo, sqlite, mysql, curl, dom, json, zip, gd

**Passos:**

1. **Checkout** - Clonar código
2. **Setup PHP** - Instalar versão PHP especificada
3. **Cache Composer** - Cache de dependências
4. **Install Dependencies** - `composer install`
5. **Setup Database** - Gerar chave, rodar migrations
6. **Run Tests** - `./vendor/bin/phpunit --coverage-text`
7. **Upload Coverage** - Codecov integration

**Comando Executado:**
```bash
php artisan migrate --env=testing --database=sqlite_testing
./vendor/bin/phpunit --coverage-text --coverage-clover=coverage.xml
```

**Configuração de Database:**
```php
// config/database.php (testing)
'sqlite_testing' => [
    'driver' => 'sqlite',
    'database' => ':memory:',  // RAM - rápido
],
```

#### 2️⃣ **Job: code-quality** (Qualidade de Código)

**Objetivo:** Validar PSR-12 compliance

**Passos:**

1. **Checkout**
2. **Setup PHP** - Versão 8.2
3. **Install Dependencies**
4. **Pint Verification** - `./vendor/bin/pint --test`
5. **PHPStan Analysis** (opcional) - Análise estática

**Comando:**
```bash
./vendor/bin/pint --test
```

**Resultado Esperado:**
```
✓ All files satisfy the Pint code style configuration.
```

#### 3️⃣ **Job: swagger** (Documentação)

**Objetivo:** Gerar documentação OpenAPI

**Passos:**

1. **Checkout**
2. **Setup PHP**
3. **Install Dependencies**
4. **Generate Swagger** - `php artisan l5-swagger:generate`

**Output:** `public/openapi.json`

#### 4️⃣ **Job: summary** (Resumo Final)

**Objetivo:** Status consolidado do pipeline

Depende de todos os 3 jobs anteriores.

---

## 🌐 Configurando no GitHub

### 1. Push do Código

```bash
# Adicionar arquivos
git add .github/workflows/ci.yml .env.testing

# Commit
git commit -m "chore: add CI/CD pipeline with GitHub Actions"

# Push para repository
git push origin main
```

### 2. Ativar GitHub Actions

1. Ir para: **Settings** → **Actions** → **General**
2. Certificar que **Allow all actions and reusable workflows** está habilitado
3. Clicar em **Save**

### 3. Visualizar Execução

1. Ir para aba **Actions**
2. Ver pipeline rodando
3. Clicar em cada job para ver logs detalhados

---

## 📊 Resultados do Pipeline

### Sucesso ✅

```
✅ All tests passed
✅ Code style compliant (PSR-12)
✅ Swagger documentation generated
✅ Coverage uploaded
```

### Falha ❌

Se algum job falhar:

1. Clicar no job
2. Ver logs da falha
3. Revisar o código
4. Fazer commit com correção
5. Push automático roda novamente

---

## 🔐 GitHub Secrets (Opcional)

Para usar serviços externos, adicione secrets em **Settings** → **Secrets and variables** → **Actions**:

```bash
# Exemplos (para deploy futuro)
CODECOV_TOKEN
DOCKER_REGISTRY_TOKEN
DEPLOYMENT_SSH_KEY
```

Usar no workflow:
```yaml
- name: Deploy
  env:
    DEPLOY_KEY: ${{ secrets.DEPLOYMENT_SSH_KEY }}
  run: ./deploy.sh
```

---

## 📋 Configuração Local

### Rodar Pipeline Localmente

**Instalar Act** (simula GitHub Actions):

```bash
# Windows (Chocolatey)
choco install act-cli

# macOS
brew install act

# Linux
curl https://raw.githubusercontent.com/nektos/act/master/install.sh | bash
```

**Executar:**

```bash
# Rodar todo o pipeline
act push

# Rodar job específico
act push -j test

# Com debug
act push -v
```

---

## 🛠️ Troubleshooting

### Problema: Testes Falhando

**Solução:**

```bash
# 1. Verificar logs do workflow
git push origin develop

# 2. Ir a GitHub Actions → ver erro

# 3. Reproduzir localmente
php artisan migrate --env=testing
./vendor/bin/phpunit

# 4. Corrigir e fazer push novamente
git commit -am "fix: test failure"
git push
```

### Problema: Pint Reclamando

**Solução:**

```bash
# Auto-corrigir localmente
./vendor/bin/pint

# Commitar
git add .
git commit -m "style: pint auto-fix"
git push
```

### Problema: Swagger Não Gerado

**Solução:**

```bash
# Regenerar localmente
php artisan l5-swagger:generate

# Verificar
ls public/openapi.json
```

---

## 📈 Métricas & Monitoring

### Coverage Report

Após cada teste, pode acessar em:

- **Codecov Dashboard:** https://codecov.io (se integrado)
- **Local:** `coverage.xml`

```bash
# Gerar localmente
./vendor/bin/phpunit --coverage-html coverage/
open coverage/index.html
```

### Histórico de Builds

**GitHub Actions → All Workflows:**

- ✅ Builds bem-sucedidos (verde)
- ❌ Builds falhados (vermelho)
- ⏳ Builds em progresso (amarelo)

---

## 🚀 Próximos Passos (Futuro)

### Deploy Automático

```yaml
deploy:
  runs-on: ubuntu-latest
  needs: [test, code-quality]
  if: github.ref == 'refs/heads/main'
  steps:
    - uses: actions/checkout@v4
    - name: Deploy to Production
      run: ./scripts/deploy.sh
```

### Notifications

```yaml
- name: Slack Notification
  uses: slackapi/slack-github-action@v1
  with:
    webhook-url: ${{ secrets.SLACK_WEBHOOK }}
```

### Docker Build & Push

```yaml
- name: Build Docker Image
  run: docker build -t myapp:latest .
- name: Push to Registry
  run: docker push myapp:latest
```

---

## 📚 Referências

- [GitHub Actions Docs](https://docs.github.com/en/actions)
- [Setup PHP Action](https://github.com/shivammathur/setup-php)
- [Codecov Action](https://github.com/codecov/codecov-action)
- [Laravel Testing](https://laravel.com/docs/testing)
- [Laravel Pint](https://laravel.com/docs/pint)

---

**Status:** ✅ Implementado e Testado  
**Última Atualização:** Junho 2026
