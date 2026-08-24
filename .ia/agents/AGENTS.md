# Contexto geral dos agentes

## Produto

O TechOS é um sistema de gestão de assistência técnica e ordens de serviço.
Ele concentra empresas, clientes, funcionários, dispositivos, planos e
operações de suporte em uma plataforma MVC em PHP.

## Estado atual

- A área pública apresenta a proposta do sistema, cadastro e login.
- O cadastro público pode criar usuários e funcionários associados ao fluxo de atendimento.
- A área autenticada em `app/` deve centralizar o acesso do cliente/usuário e o acompanhamento de serviços.
- O painel `/admin` concentra a gestão de usuários, empresas, funcionários, dispositivos, ordens e FAQs.
- O domínio do projeto inclui empresas, funcionários, dispositivos, categorias, planos, ordens de serviço e FAQ.
- O modelo SQL e os scripts estão localizados em `data-base/` e devem servir como referência para estrutura e integridade.

## Perfis e sessões

| Perfil | Tipo | Entrada | Escopo de sessão |
|---|---:|---|---|
| Administrador | 1 | `/admin` | `admin` |
| Usuário interno / técnico | 2 | `/app` | padrão |
| Cliente | 3 | `/app` | padrão |

A autenticação deve ser validada no backend por JWT e pela regra de permissões,
never trusting user data only in browser.

## Arquitetura a preservar

- `api/`: backend PHP que responde JSON.
- `api/index.php`: front controller e definição das rotas.
- `api/source/Controller/`: autenticação, validação e orquestração.
- `api/source/Models/`: SQL e regras de persistência.
- `views/assets/_common/`: classes HTTP, domínio, storage e guards.
- `views/assets/public/`: landing page e autenticação inicial.
- `views/assets/app/`: área autenticada do usuário/cliente.
- `views/assets/admin/`: painel administrativo.

Controllers não devem conter SQL. Models devem usar prepared statements.
Views não devem acessar o banco diretamente nem decidir permissões.

## Convenções de frontend

- Documentação e textos em Português do Brasil.
- Código, classes, métodos e arquivos em English.
- HTML semântico, CSS separado e listeners em JavaScript.
- Sem jQuery ou eventos inline.
- Usar `HttpClientBase` em todas as chamadas de API.
- Manter mocks claramente identificados e separados dos clientes HTTP.

## Próximas evoluções prováveis

- Gestão completa de empresas e funcionários.
- Cadastro e acompanhamento de dispositivos e categorias.
- Criação, edição e acompanhamento de ordens de serviço.
- Integração real dos planos e FAQs com o banco.
- Relatórios e indicadores para operação e administração.
