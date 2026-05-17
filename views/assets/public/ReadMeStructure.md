# TechOS — Site estático

Site institucional da TechOS em HTML/CSS/JS puro, seguindo as convenções
do projeto MVC (PHP) descritas em `AGENTS.md` e `copilot-instructions.md`.

## Estrutura

```
techos-static/
├── index.html         ← Home
├── recursos.html
├── precos.html
├── sobre.html
├── contato.html
├── login.html
├── cadastro.html
└── views/
    └── assets/
        ├── _common/
        │   ├── styles/base.css     ← Tokens, layout, header, footer, botões
        │   └── scripts/nav.js      ← Menu mobile (compartilhado)
        └── public/
            ├── styles/             ← CSS por tela
            │   ├── home.css
            │   ├── recursos.css
            │   ├── precos.css
            │   ├── sobre.css
            │   ├── contato.css
            │   └── auth.css
            ├── scripts/            ← JS por tela
            │   ├── contato.js
            │   ├── login.js
            │   └── cadastro.js
            └── images/
                └── hero-dashboard.jpg
```

## Padrões adotados

- Conteúdo em **Português do Brasil**, código em **inglês**.
- HTML **100% semântico**: sem `<div>` e sem `<table>` para layout.
- Sem `jQuery` e sem eventos inline (`onclick`, etc.).
- JS usa `document.querySelector` / `addEventListener`.
- Comentários `// TODO: substituir por HttpClientBase.js` marcam os pontos
  onde a integração com a API deverá ser feita posteriormente.

## Rodando localmente

Abra `index.html` direto no navegador, ou sirva a pasta com qualquer
servidor estático:

```
python3 -m http.server 8000
```
