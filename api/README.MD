# TechOS API

Base: `http://localhost/techos/api`

---

## Respostas

Tudo que a API devolve segue esse formato:

```json
{
  "code": 200,
  "type": "success",
  "status": "success",
  "message": "alguma mensagem",
  "data": {}
}
```

---

## Autenticacao

Algumas rotas exigem token JWT. Faz login no `/users/login`, pega o token que volta e manda no cabecalho `token: SEU_TOKEN`.

**Niveis:** Admin (`type_id = 1`) e Usuario comum (`type_id = 2`). Rotas com `-admin` no nome sao so pra admin.

---

## Rotas

### Usuarios

**Cadastrar usuario**
```
POST /users/register
```
```json
{ "name": "Joao", "email": "joao@email.com", "password": "123" }
```

**Cadastro admin**
```
POST /users/register-admin
```

**Login**
```
POST /users/login
```
```json
{ "email": "joao@email.com", "password": "123" }
```

**Login admin**
```
POST /users/login-admin
```
Mesma coisa, mas so aceita admin.

**Atualizar dados**
```
PUT /users/update
```
```json
{ "name": "Joao Novo", "email": "joao.novo@email.com" }
```

---

### Empresas

```
GET    /companies/list              → Lista todas
GET    /companies/list/{id}         → Uma especifica
POST   /companies                   → Criar
PUT    /companies/{id}              → Atualizar
DELETE /companies/{id}              → Deletar (soft delete)
```

**Criar empresa:**
```json
{ "cnpj": "123", "name": "Minha Empresa", "email": "contato@empresa.com", "owner_id": 1, "plan_id": 1 }
```

**Listagem retorna:**
```json
{ "data": [{ "id": 1, "cnpj": "123", "name": "Minha Empresa", "owner_name": "Joao", "plan_name": "Basico" }] }
```

---

### Funcionarios

```
GET    /employees/list              → Lista todos
GET    /employees/list/{id}         → Um especifico
POST   /employees                   → Criar
PUT    /employees/{id}              → Atualizar
DELETE /employees/{id}              → Deletar (soft delete)
```

**Criar:**
```json
{ "user_id": 1, "company_id": 1 }
```

---

### Dispositivos

```
GET    /devices/list                → Lista todos
GET    /devices/list/{id}           → Um especifico
POST   /devices                     → Criar
PUT    /devices/{id}                → Atualizar
DELETE /devices/{id}                → Deletar (soft delete)
```

**Criar:**
```json
{ "user_id": 1, "category_id": 1, "serial_number": "SN123", "model": "iPhone 14", "brand": "Apple" }
```

**Listagem retorna nome do usuario e da categoria junto.**

---

### Categorias de Dispositivos

```
GET    /devices-categories/list     → Lista todas
GET    /devices-categories/list/{id}
POST   /devices-categories          → Criar
PUT    /devices-categories/{id}     → Atualizar
DELETE /devices-categories/{id}     → Deletar
```

**Criar:**
```json
{ "name": "Tablet" }
```

Se tentar deletar uma categoria que tem dispositivos vinculados, a API devolve 400 e nao deixa.

---

### Ordens de Servico (OS)

```
GET    /serviceOrders/list          → Lista todas
GET    /serviceOrders/list/{id}     → Uma especifica
POST   /serviceOrders               → Criar
PUT    /serviceOrders/{id}          → Atualizar
DELETE /serviceOrders/{id}          → Deletar (soft delete)
```

**Criar:**
```json
{
  "user_id": 1,
  "device_id": 1,
  "company_id": 1,
  "defect": "Tela quebrada",
  "status": "aberta",
  "price": 350.00,
  "photo": "foto.jpg"
}
```

**Status possiveis:** `aberta`, `aguardando_peca`, `em_andamento`, `cancelada`, `concluida`

---

### Planos

```
GET    /plans/list                  → Lista todos
GET    /plans/list/{id}
POST   /plans                       → Criar
PUT    /plans/{id}                  → Atualizar
DELETE /plans/{id}                  → Deletar (soft delete)
```

**Criar:**
```json
{ "name": "Plano Premium", "price": 99.90 }
```

---

### FAQs

```
GET    /faqs/list                   → Lista todas
GET    /faqs/list/{id}
POST   /faqs/insert                 → Criar
PUT    /faqs/update/{id}            → Atualizar
DELETE /faqs/{id}                   → Deletar (soft delete)
```

**Criar:**
```json
{ "faqs_category_id": 1, "question": "Pergunta?", "answer": "Resposta." }
```

---

### Categorias de FAQ

```
GET    /faqs-categories/list        → Lista todas
GET    /faqs-categories/list/{id}
POST   /faqs-categories             → Criar
PUT    /faqs-categories/update/{id} → Atualizar
DELETE /faqs-categories/{id}        → Deletar
```

**Criar:**
```json
{ "name": "Suporte" }
```

Se tiver FAQ vinculada a categoria, nao deixa deletar.

---

### Produtos

```
GET /products/list              → Lista todos
GET /products/list/{id}
```

---

