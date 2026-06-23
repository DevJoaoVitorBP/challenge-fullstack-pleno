# 🚀 Guia Rápido: Deploy no GitHub e Ativar CI/CD

## 1️⃣ Preparar o Repositório GitHub

### Se ainda não tem o repositório

```bash
# Ir ao diretório do projeto
cd c:\Projeto\Challenge\challenge-fullstack-pleno\ecommerce-api

# Inicializar git (se não tiver)
git init

# Adicionar remote
git remote add origin https://github.com/SEU_USUARIO/ecommerce-api.git

# Renomear branch para main
git branch -M main
```

### Se já tem repositório clonado

```bash
cd c:\Projeto\Challenge\challenge-fullstack-pleno\ecommerce-api

# Verificar remote
git remote -v
```

---

## 2️⃣ Adicionar Arquivos de CI/CD

Já estão criados:
- ✅ `.github/workflows/ci.yml` - Pipeline principal
- ✅ `.env.testing` - Configuração para testes

Apenas commitar:

```bash
# Status atual
git status

# Ver o que foi adicionado
git diff .github/workflows/ci.yml
git diff .env.testing
```

---

## 3️⃣ Fazer Commit e Push

```bash
# Staged
git add .github/workflows/ci.yml .env.testing

# Commit
git commit -m "chore: add GitHub Actions CI/CD pipeline

- Added .github/workflows/ci.yml with test, code-quality, and swagger jobs
- Tests run on PHP 8.2 and 8.3
- PSR-12 validation with Laravel Pint
- Swagger documentation auto-generation
- Coverage reporting to Codecov
- Added .env.testing for test environment"

# Push para main
git push origin main
```

---

## 4️⃣ Ativar GitHub Actions

### No GitHub UI:

1. Abra https://github.com/SEU_USUARIO/ecommerce-api
2. Vá para **Settings** (aba engrenagem)
3. Vá para **Actions** → **General**
4. Certifique que **Allow all actions and reusable workflows** está habilitado
5. Clique **Save**

### Verificar se funcionou:

1. Vá para aba **Actions**
2. Você deve ver um workflow rodando: "chore: add GitHub Actions CI/CD pipeline"
3. Espere completar (deve levar ~2 minutos)
4. Se tudo verde ✅ = Sucesso!

---

## 5️⃣ Visualizar Resultados

### Dashboard do Workflow

```
https://github.com/SEU_USUARIO/ecommerce-api/actions
```

Você verá:
- ✅ **test** - Testes em PHP 8.2, 8.3
- ✅ **code-quality** - Pint + PHPStan
- ✅ **swagger** - Geração OpenAPI
- ✅ **summary** - Status consolidado

### Ver Logs de Um Job

1. Clique no workflow
2. Clique no job (ex: "test")
3. Expanda "Run PHPUnit Tests"
4. Veja output completo

---

## 6️⃣ Próximos Pushes

Toda vez que fizer push:

```bash
git add .
git commit -m "feat: implementar nova funcionalidade"
git push origin main
```

O pipeline **roda automaticamente**:
- Testa código
- Valida estilo
- Gera docs
- Relata problemas

---

## ✅ Checklist

- [ ] Repositório GitHub criado
- [ ] Código pusheado (`git push origin main`)
- [ ] GitHub Actions habilitado (Settings → Actions)
- [ ] Workflow rodou com sucesso
- [ ] Todos 3 jobs ficaram ✅ (test, code-quality, swagger)
- [ ] Coverage enviado para Codecov

---

## 🐛 Troubleshooting

### Workflow não aparece

```bash
# Verificar se os arquivos foram commitados
git log --oneline -n 5

# Se não, fazer push novamente
git status
git add .
git push origin main
```

### Job falhando

1. Clicar no workflow falhado
2. Clicar no job
3. Ver qual comando falhou
4. Reproduzir localmente:

```bash
# Exemplo: se Pint falhando
./vendor/bin/pint --test

# Corrigir
./vendor/bin/pint

# Commit
git add .
git commit -m "style: pint auto-fix"
git push origin main
```

### Erro de cache/dependências

```bash
# Limpar cache no GitHub Actions
# Ir para Settings → Actions → General → Clear all caches

# Fazer push novamente
git commit --allow-empty -m "chore: trigger CI/CD"
git push origin main
```

---

## 📊 Integrações Futuras

### Codecov (Coverage Reports)

1. Ir para https://codecov.io
2. Conectar com GitHub
3. Adicionar secret em Settings → Secrets

```yaml
# No ci.yml
- name: Upload Coverage
  uses: codecov/codecov-action@v3
  with:
    file: ./coverage.xml
    flags: unittests
```

### Slack Notifications

```yaml
- name: Notify Slack
  uses: slackapi/slack-github-action@v1
  with:
    webhook-url: ${{ secrets.SLACK_WEBHOOK }}
```

---

## 📚 Referências

- [GitHub Actions](https://docs.github.com/en/actions)
- [Setup PHP Action](https://github.com/shivammathur/setup-php)
- [Laravel Testing](https://laravel.com/docs/testing)
- [Codecov](https://codecov.io)

---

**Status:** ✅ Ready to Use  
**Última Atualização:** Junho 2026
