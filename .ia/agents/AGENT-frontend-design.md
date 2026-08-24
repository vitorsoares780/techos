# Agent de Frontend Design

## Objetivo

Planejar e implementar as interfaces do TechOS sem misturar a área pública, a aplicação autenticada e o painel administrativo.

## Regras permanentes

- Textos e documentação em Português do Brasil.
- Código, identificadores, classes, métodos e arquivos em English.
- HTML semântico e CSS em arquivo separado.
- Não usar jQuery nem eventos inline.
- Usar `document.querySelector` e listeners em JavaScript.
- Integrar API por classes que herdam de `HttpClientBase`.
- Não colocar regra de negócio ou autorização somente na View.

## Áreas da aplicação

### Área pública

- Entrada: `index.html`.
- Público: visitantes, clientes e empresas ainda não autenticados.
- Objetivo: apresentar o sistema, capturar leads, permitir login e cadastro de clientes e funcionários.
- Telas previstas: home, sobre, contato, cadastro de empresa, cadastro de usuário, login, FAQ.
- Ações principais: conhecer o sistema, solicitar suporte, acompanhar ordens e entrar no painel.

### Área autenticada (`app`)

- Entrada: `app/dashboard.html`.
- Público: usuários autenticados, incluindo clientes e usuários internos com acesso restrito.
- Objetivo: acompanhar ordens de serviço, consultar dispositivos, abrir solicitações e visualizar status.
- Telas previstas: dashboard, perfil, listagem de OS, detalhes da OS, cadastro/edição de serviços e histórico.
- Componentes principais: menu lateral, cards de resumo, filtros, listas, formulários e detalhes de atendimento.
- Ação principal esperada: visualizar e acompanhar os serviços, enviar informações e monitorar status.

### Área administrativa (`admin`)

- Entrada: `admin/painel.html`.
- Público: usuários `type_id = 1` e gestores.
- Objetivo: gerenciar empresas, funcionários, dispositivos, planos, ordens e FAQs.
- Telas previstas: painel geral, ordens de serviço, empresas, funcionários, dispositivos, categorias e relatórios.
- Componentes principais: tabelas semânticas, filtros, formulários, indicadores e gestão de acesso.
- Ação principal esperada: administração de dados, aprovação de serviços e gestão operacional.

## Organização de arquivos

```text
views/assets/
├── _common/
│   ├── classes/     ← HttpClientBase e classes de domínio
│   └── scripts/     ← storage, guards e utilitários
├── public/
│   ├── scripts/
│   └── styles/
├── app/
│   ├── scripts/
│   └── styles/
└── admin/
    ├── scripts/
    └── styles/
```

## Direção visual

- Manter identidade profissional, limpa e confiável, com foco em assistência técnica e operação.
- A área pública deve transmitir credibilidade e clareza, com CTA de cadastro e login.
- A área do usuário deve priorizar leitura rápida de ordens, filtros e status.
- A área administrativa deve priorizar indicadores, tabelas, filtros e gestão por perfis.
- Todos os fluxos devem possuir estados de carregamento, vazio, erro e sucesso.

## Navegação e organização visual

- Estrutura principal: menu superior ou lateral, cards e listagens de serviços.
- Fluxo típico: home → login/cadastro → dashboard → detalhes da OS → atualização de status.
- Hierarquia visual: títulos, subtítulos, instruções, botões de ação e feedback claro.
- Estados importantes: vazio, carregando, erro visual e sucesso.

## Responsividade e acessibilidade

- Breakpoints desejados: mobile, tablet e desktop.
- Ajustes esperados: menus, cards e formulários com adaptação por tamanho de tela.
- Cuidados de acessibilidade: contraste, legibilidade, ordem lógica e foco visível.
- Elementos semânticos esperados: `header`, `nav`, `main`, `section`, `article`, `aside`, `footer`, `form`, `fieldset` e `button`.

## Identidade visual

- Paleta principal: azul, cinza claro, branco e detalhes em verde/laranja para ações e estados.
- Tipografia: fontes sem serifa para títulos e texto, com escala clara para hierarquia.
- Referências visuais: sistemas de gestão de suporte e assistência técnica, dashboards operacionais e interfaces de manutenção.
- Sensação esperada: seriedade, confiança, velocidade, organização e simplicidade.

## Limite entre etapa atual e integração futura

- Agora: criar HTML semântico, CSS e interações estáticas em JavaScript.
- Depois: integrar com a API usando `HttpClientBase.js`, tratar erros assíncronos e renderizar dados dinamicamente.
- Ao propor código, a IA deve separar o que é mock estático do que será substituído por dados reais depois.

## Instrução final para estudantes e IA

Antes de implementar qualquer tela, registre o máximo de clareza possível sobre o fluxo e os dados. Se uma informação ainda não estiver definida, use `[a definir]` em vez de inventar requisitos.
