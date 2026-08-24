# Instruções centrais do TechOS

## Contexto do produto

O TechOS é um sistema de gestão de assistência técnica em PHP MVC para
empresas, clientes, técnicos e administração. O objetivo principal é controlar
ordens de serviço, dispositivos, funcionários, planos e cadastros associados.

As áreas existentes são:

- `index.html`: área pública, apresentação do sistema, cadastro de empresas e login;
- `app/dashboard.html`: área autenticada do cliente e/ou usuário interno;
- `admin/painel.html`: painel administrativo da operação e gestão do sistema.

## Idioma e nomenclatura

- Textos de interface e documentação: Português do Brasil.
- Código, identificadores, classes, métodos e nomes de arquivos: English.
- Preserve a nomenclatura em `snake_case` no banco e nos payloads da API.

## Arquitetura

Preserve as fronteiras MVC:

| Camada | Responsabilidade |
|---|---|
| Controller | Rotas, autenticação, validação e respostas JSON |
| Model | SQL, prepared statements e regras de persistência |
| View/assets | Interface, eventos e consumo da API |

Fluxo:

```text
HTML/JavaScript → Apache/API → api/index.php → Controller → Model → MySQL
                                                           ↓
                                                     JSON da API
```

Não coloque SQL em JavaScript, HTML ou controllers. Não coloque regra de
autorização apenas no frontend.

## Frontend

- Use HTML semântico, CSS separado e JavaScript orientado a objetos.
- Não use jQuery nem eventos inline como `onclick`.
- Use `document.querySelector` e listeners registrados em JavaScript.
- Requisições devem reutilizar `views/assets/_common/classes/HttpClientBase.js`.
- Classes de domínio HTTP ficam em `views/assets/_common/classes/`.
- Código específico deve permanecer isolado em `public`, `app` ou `admin`.
- Mocks devem ser identificados e não podem substituir silenciosamente uma API integrada.

Pastas atuais:

```text
views/assets/
├── _common/  ← HTTP, classes de domínio, sessão e guards
├── public/   ← landing page, login e cadastro inicial
├── app/      ← área autenticada do cliente/usuário
├── admin/    ← painel administrativo e gestão operacional
└── ...
```

## Autenticação e autorização

Tipos de acesso usados pelo produto:

- `type_id = 1`: administrador;
- `type_id = 2`: usuário interno/funcionário/técnico;
- `type_id = 3`: cliente ou responsável pela empresa.

As sessões são separadas no `SessionStorage`:

- sessão padrão: app/usuário autenticado;
- escopo `admin`: administração.

Use `AuthGuard` com o escopo e o tipo esperado da área. O backend deve
validar o tipo pelo JWT e derivar o usuário autenticado; nunca confie em um
`user_id` enviado pelo cliente sem verificar o token.

Rotas importantes:

```text
POST /users/register
POST /users/register-admin
POST /users/login
POST /users/login-admin
GET  /companies/list
POST /companies
GET  /devices/list
POST /devices
GET  /serviceOrders/list
POST /serviceOrders
GET  /faqs/list
POST /faqs/insert
```

## Banco de dados

O modelo de dados do projeto está espalhado em `data-base/` e é a fonte
primária para schema, relacionamentos e regras de integridade. O sistema
contem empresas, usuários, funcionários, dispositivos, categorias, planos,
ordens de serviço e FAQs.

Use prepared statements nos Models e preserve as chaves estrangeiras,
índices e restrições presentes no banco.

## Validação

Depois de alterações:

- valide JavaScript com `node --check`;
- valide PHP com `php -l` no container;
- execute `git diff --check`;
- teste as rotas protegidas sem token e com o tipo de usuário correto;
- confirme que as áreas não compartilham sessões indevidamente.
