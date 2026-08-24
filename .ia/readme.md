# 🤖 `.ia/` — Central de instruções para IA do projeto TechOS

> 📖 **Leia este arquivo primeiro** — ele explica **como** e **por que** usar agents e skills.
> 🎯 **Público:** equipe de desenvolvimento e ferramentas de IA do projeto

---

## 🧭 Visão geral

A pasta `.ia/` funciona como uma base de contexto para a IA trabalhar no TechOS sem perder as regras e a arquitetura do projeto.

| O que é | Função | Exemplo |
|---|---|---|
| **Agent** | Contexto do produto e planejamento | "Qual é o objetivo da área administrativa?" |
| **Skill** | Regras de implementação | "Como criar uma tela sem quebrar o MVC e o HTML semântico?" |

---

## 📂 Estrutura da pasta `.ia/`

```
.ia/
├── readme.md                  ← Visão geral do uso da pasta
├── copilot-instructions.md    ← Instruções resumidas para qualquer IA
│
├── agents/                    ← Contexto do projeto
│   ├── AGENTS.md              ← Visão geral do TechOS
│   └── AGENT-frontend-design.md ← Planejamento visual do front-end
│
└── skills/                    ← Regras práticas
    └── SKILL-frontend-design.md
```

---

## 🎯 Como usar no projeto

Antes de gerar qualquer interface, a IA deve ler:

1. `.ia/copilot-instructions.md`
2. `.ia/agents/AGENTS.md`
3. `.ia/agents/AGENT-frontend-design.md`
4. a skill aplicável em `.ia/skills/`

Isso mantém o trabalho consistente com a arquitetura TechOS e com o domínio real da aplicação.

---

## 🏗️ Domínio do TechOS

O TechOS é um sistema de gestão de assistência técnica e ordens de serviço.
Ele envolve:

- clientes e empresas;
- funcionários e técnicos;
- dispositivos e categorias;
- planos e empresas;
- ordens de serviço;
- FAQs e administração.

A IA deve manter os nomes do domínio em inglês no código e a comunicação em português brasileiro para interface e documentação.

---

## ⚠️ Regras importantes

1. **Texto em Português do Brasil**
2. **Código e nomes em English**
3. **HTML semântico e CSS em arquivo separado**
4. **Sem jQuery e sem eventos inline**
5. **Usar `HttpClientBase` para API**
6. **Manter separação entre público, app e admin**
7. **Não misturar regra de negócio na View**

---

## 🔗 Resumo visual

```
┌──────────────────────────────────────────────────────────────┐
│                                                              │
│   📘 CONTEXTO → .ia/copilot-instructions.md                  │
│                                                              │
│   📋 AGENT → O que é o projeto?                             │
│   ├── .ia/agents/AGENTS.md                                  │
│   └── .ia/agents/AGENT-frontend-design.md                  │
│                                                              │
│   🔧 SKILL → Como implementar corretamente                  │
│   └── .ia/skills/SKILL-frontend-design.md                  │
│                                                              │
│   💬 IA → gera telas, rotas, modelos e integrações          │
│                                                              │
└──────────────────────────────────────────────────────────────┘
```

---

> 💡 **Dica final:** a IA deve sempre pensar primeiro em domínio, autenticação e organização do projeto TechOS antes de escrever código.
