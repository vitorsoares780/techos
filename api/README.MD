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

**Niveis:** Admin, Usuario comum e Cliente. Rotas com `-admin` no nome sao so pra admin.

As permissões das rotas de ordens de serviço variam por operação e estão
descritas abaixo. A edição de perfil e foto altera somente os dados do usuário
autenticado, sem aceitar um identificador de outro perfil no payload.

---

## Rotas

### Usuarios

**Cadastrar cliente** (sempre type_id = 3)
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
Retorna: `id`, `name`, `photo`, `token`

**Login admin**
```
POST /users/login-admin
```
Mesma coisa, mas so aceita admin.

**Atualizar os próprios dados** (exige token válido de administrador, funcionário ou cliente)
```
PUT /users/update
```
```json
{ "name": "Joao Novo", "email": "joao.novo@email.com" }
```

**Atualizar foto do próprio perfil** (exige token válido de usuário autenticado)
```
POST /users/photo
Content-Type: multipart/form-data
Campo: photo (uma imagem JPEG, PNG ou WebP; máximo de 5 MB)
```
O identificador do perfil vem do token. A resposta retorna `data.photo` com a URL
pública da imagem. Configure o PHP com `upload_max_filesize=6M` e
`post_max_size=8M` ou valores maiores.

---

### Empresas

```
GET    /companies/list              → Lista todas
GET    /companies/list/{id}         → Uma especifica
POST   /companies                   → Criar (exige token, nivel 2+)
PUT    /companies/{id}              → Atualizar (exige token, admin)
DELETE /companies/{id}              → Deletar (soft delete, exige token, admin)
```

**Criar empresa:**
```json
{ "cnpj": "123", "name": "Minha Empresa", "email": "contato@empresa.com", "owner_id": 1, "plan_id": 1 }
```

**Listagem retorna:**
```json
{ "data": [{ "id": 1, "cnpj": "123", "name": "Minha Empresa", "email": "contato@empresa.com", "owner_name": "Joao", "plan_name": "Basico" }] }
```

---

### Funcionarios (exige token, admin)

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

### Dispositivos (exige token, admin)

```
GET    /devices/list                → Lista todos
GET    /devices/list/{id}           → Um especifico
POST   /devices                     → Criar
PUT    /devices/{id}                → Atualizar
DELETE /devices/{id}                → Deletar (soft delete)
```

**Criar:**
```json
{ "user_id": 1, "category_id": 1, "serial_number": "SN123", "name": "iPhone 14" }
```

**Listagem retorna:** `id`, `serial_number`, `name`, `category_name`, `user_name`

---

### Categorias de Dispositivos (exige token, admin)

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
GET    /serviceOrders/list          → Listar conforme o perfil (exige token)
GET    /serviceOrders/list/{id}     → Consultar conforme o perfil (exige token)
POST   /serviceOrders               → Criar (exige token; cliente cria para si, admin/funcionário informam o cliente)
PUT    /serviceOrders/{id}          → Atualizar pelo painel (exige token de admin)
PUT    /serviceOrders/{id}/employee-update → Atualizar pelo app (exige token de admin ou funcionário)
DELETE /serviceOrders/{id}          → Deletar (soft delete, exige token, admin)
```

Administradores (type_id 1) e funcionários (type_id 2) podem editar OS pelo
app. Funcionários só podem editar ordens de empresas às quais estão vinculados
por um registro ativo em `employees`; administradores podem editar qualquer
OS. Clientes (type_id 3) não podem editar ordens. A listagem e consulta
retornam todas as OS para administradores, OS das empresas vinculadas para
funcionários e somente as OS próprias para clientes.

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

#### Galeria de imagens da ordem

Cada imagem é armazenada como arquivo e seus metadados ficam em
`service_order_images`, relacionada à ordem por `service_order_id`.

```
GET    /serviceOrders/{serviceOrderId}/images
POST   /serviceOrders/{serviceOrderId}/images
DELETE /serviceOrders/{serviceOrderId}/images/{imageId}
```

As rotas GET e POST exigem autenticação e acesso à OS. Administradores têm
acesso a todas as ordens; funcionários acessam ordens de empresas às quais
estão vinculados; clientes acessam somente as próprias ordens. O DELETE exige
token de administrador. O POST recebe
`multipart/form-data` com o campo `image` (uma imagem por requisição; JPEG,
PNG ou WebP; máximo de 5 MB). A tela pode enviar vários arquivos em requisições
sequenciais. Respostas seguem o envelope padrão; a listagem retorna `data`
como uma lista (inclusive quando vazia), com `id`, `service_order_id`,
`image_url`, `alt_text`, `position` e `created_at`. Somente administradores
podem remover imagens; a remoção confere a associação entre a imagem e a OS.
Excluir (soft delete) uma ordem pelo painel também remove os registros e
arquivos da galeria.

---

### Planos

```
GET    /plans/list                  → Lista todos
GET    /plans/list/{id}
POST   /plans                       → Criar
PUT    /plans/{id}                  → Atualizar
DELETE /plans/{id}                  → Deletar (delete fisico, nao soft delete)
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
POST   /faqs/insert                 → Criar (exige token, admin)
PUT    /faqs/update/{id}            → Atualizar (exige token, admin)
DELETE /faqs/{id}                   → Deletar (soft delete, exige token, admin)
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
POST   /faqs-categories             → Criar (exige token, admin)
PUT    /faqs-categories/update/{id} → Atualizar (exige token, admin)
DELETE /faqs-categories/{id}        → Deletar (exige token, admin)
```

**Criar:**
```json
{ "name": "Suporte" }
```

Se tiver FAQ vinculada a categoria, nao deixa deletar.

---
