# TechOS — Parte B: POO Prototípica

## Objetivo

Nesta etapa foram implementadas funcionalidades reutilizáveis utilizando **POO prototípica em JavaScript**, sem o uso de `class`.

O objetivo é demonstrar a criação de objetos a partir de protótipos utilizando `Object.create()`, permitindo que diferentes objetos compartilhem os mesmos métodos definidos em um único protótipo.

Foram implementadas duas funcionalidades:

- `Toast`
- `FormController`

---

## 1. Toast

Arquivo:

```text
views/assets/_common/classes/Toast.js
```

O `Toast` é responsável por apresentar mensagens temporárias ao usuário de acordo com o resultado de uma operação.

São suportados três tipos de mensagem:

- `success` — sucesso
- `warning` — aviso
- `error` — erro

### Protótipo

A funcionalidade é definida através de um objeto utilizado como protótipo:

```js
const ToastPrototype = {
    init() {
        // ...
    },

    show(type, response, duration = 4000) {
        // ...
    },

    success(response, duration = 4000) {
        return this.show("success", response, duration);
    },

    warning(response, duration = 4000) {
        return this.show("warning", response, duration);
    },

    error(response, duration = 4000) {
        return this.show("error", response, duration);
    }
};
```

### Criação dos objetos

Os objetos são criados utilizando `Object.create()`:

```js
const toastPrincipal = Object.create(ToastPrototype);
const toastSecundario = Object.create(ToastPrototype);
```

Dessa forma, os dois objetos utilizam o mesmo `ToastPrototype` e compartilham seus métodos.

### Utilização

```js
toastPrincipal.success({
    message: "Login realizado com sucesso!"
});

toastPrincipal.warning({
    message: "Informe e-mail e senha para continuar."
});

toastPrincipal.error({
    message: "Não foi possível realizar a operação."
});
```

### Tratamento das respostas

O Toast aceita tanto uma resposta contendo `message` diretamente:

```js
{
    message: "Operação realizada com sucesso!"
}
```

quanto uma resposta em que a mensagem está dentro de `data`:

```js
{
    data: {
        message: "Operação realizada com sucesso!"
    }
}
```

O componente extrai somente a propriedade `message`, evitando exibir o conteúdo inteiro de `data`.

Respostas inesperadas são tratadas através de erro, evitando que problemas na estrutura da resposta sejam silenciosamente ignorados.

---

## 2. FormController

Arquivo:

```text
views/assets/_common/classes/FormController.js
```

O `FormController` fornece funcionalidades reutilizáveis para trabalhar com formulários.

Entre suas responsabilidades estão:

- inicializar um formulário;
- obter os dados do formulário;
- obter o valor de um campo;
- validar campos obrigatórios;
- limpar o formulário.

### Protótipo

```js
const FormControllerPrototype = {
    init(form) {
        // ...
    },

    getData() {
        // ...
    },

    getValue(fieldName) {
        // ...
    },

    validateRequired(fields) {
        // ...
    },

    clear() {
        // ...
    }
};
```

### Criação dos objetos

```js
const loginFormController =
    Object.create(FormControllerPrototype);

const userFormController =
    Object.create(FormControllerPrototype);
```

Assim como no `Toast`, os métodos não são recriados individualmente em cada objeto. Eles são encontrados através da cadeia de protótipos.

---

## 3. Utilização no Login

As funcionalidades foram integradas à tela de Login do sistema.

```js
import { toastPrincipal }
    from "../../_common/classes/Toast.js";

import { loginFormController }
    from "../../_common/classes/FormController.js";
```

O formulário é inicializado:

```js
loginFormController.init(form);
```

A validação dos campos obrigatórios é feita através do `FormController`:

```js
const validation =
    loginFormController.validateRequired([
        "email",
        "password"
    ]);
```

Os dados do formulário também são obtidos através do controlador:

```js
const formData = loginFormController.getData();

const email = String(formData.email || "").trim();
const password = String(formData.password || "").trim();
```

O Toast é utilizado para informar diferentes estados da operação:

```js
toastPrincipal.warning({
    message: "Informe e-mail e senha para continuar."
});
```

```js
toastPrincipal.error({
    message: "Credenciais inválidas."
});
```

```js
toastPrincipal.success({
    message: "Login realizado com sucesso!"
});
```

O `setMessage()` original da tela também foi mantido para preservar o comportamento visual existente da aplicação.

---

## 4. Demonstração da POO prototípica

```text
ToastPrototype
       │
       ├── Object.create()
       │
       ├── toastPrincipal
       │
       └── toastSecundario
```

```text
FormControllerPrototype
       │
       ├── Object.create()
       │
       ├── loginFormController
       │
       └── userFormController
```

Nos dois casos, os objetos derivados possuem acesso aos métodos definidos no protótipo.

Isso demonstra o compartilhamento de comportamento através da cadeia de protótipos, sem criar uma nova `class`.

---

## 5. Diferença para a POO clássica

Na POO clássica utilizada na Parte A, as entidades são definidas através de `class`:

```js
class User {
    // ...
}
```

Na Parte B, as funcionalidades foram implementadas através de objetos utilizados como protótipos:

```js
const ToastPrototype = {
    // ...
};
```

E os objetos são derivados através de:

```js
Object.create(ToastPrototype);
```

Portanto:

```text
Parte A
class
  ↓
instâncias

Parte B
objeto protótipo
  ↓
Object.create()
  ↓
objetos derivados
```

A Parte B demonstra uma abordagem diferente de reutilização de comportamento em JavaScript.

---

## 6. Arquivos envolvidos

```text
views/
└── assets/
    └── _common/
        ├── classes/
        │   ├── Toast.js
        │   └── FormController.js
        │
        └── styles/
            └── toast.css
```

A tela de Login utiliza essas funcionalidades através de módulos JavaScript.

---

## 7. Resultado

A Parte B possui duas funcionalidades reutilizáveis implementadas com POO prototípica:

1. **Toast**
   - criado através de `Object.create()`;
   - possui mensagens de sucesso, aviso e erro;
   - trata diferentes formatos de resposta;
   - utilizado na tela de Login.

2. **FormController**
   - criado através de `Object.create()`;
   - possui métodos compartilhados para manipulação de formulários;
   - realiza validação e obtenção de dados;
   - utilizado na tela de Login.

As funcionalidades são utilizadas pelo sistema real e não apenas declaradas de forma isolada, demonstrando a aplicação prática de protótipos no frontend.
