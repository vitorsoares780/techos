# UNIDADE VIII — Orientação a Objetos no JavaScript

> **Disciplina**: Programação Web II  
> **Domínio**: Projeto ACME 3AM (e-commerce)  
> **Padrão**: Código em inglês, explicações/documentação em português  
> **Scripts**: `assets/scripts/`  
> **Styles**: `assets/styles/`  
> **HTMLs (raiz do projeto)**: `index.html`, `oo-prototypal.html`, `creation-objects.html`, `cloning-objects.html`, `mutability-objects.html`, `inheritance-prototype.html`, `constructor-functions.html`  
> **Convenção de nomes**: kebab-case para arquivos de script

---

## Estrutura de arquivos

```
poo-javascript/
│
├── index.html                    ← Página inicial com links para os tópicos
├── oo-prototypal.html            ← Abrir no navegador, ver console
├── creation-objects.html
├── cloning-objects.html
├── mutability-objects.html
├── inheritance-prototype.html
├── constructor-functions.html
│
├── assets/
│   ├── scripts/                  ← Scripts JS importados pelos HTMLs
│   │   ├── oo-prototypal.js          (8.1)
│   │   ├── creation-objects.js       (8.2)
│   │   ├── cloning-objects.js        (8.3)
│   │   ├── mutability-objects.js     (8.4)
│   │   ├── inheritance-prototype.js  (8.5)
│   │   └── constructor-functions.js  (8.6)
│   │
│   └── styles/                   ← Estilos CSS
│       └── base.css              (estilos compartilhados)
│
└── plans/                        ← Documentação e planejamento
    ├── plano-de-ensino.md
    └── poo-javascript.md
```

---

## Cronograma geral

| Semana | Tópico | Conteúdo |
|---|---|---|
| **Semana 1** | 8.1 — OO Prototípica | Cadeia de protótipos, delegação, `Object.getPrototypeOf`, `__proto__` |
| **Semana 2** | 8.2 — Criação de Objetos | Literal, `Object.create`, `Object.assign`, `Object.defineProperty` |
| **Semana 3** | 8.3 — Clonagem de Objetos | Shallow copy, deep copy, `structuredClone`, `JSON.parse(JSON.stringify)` |
| **Semana 4** | 8.4 — Mutabilidade de Objetos | `const` vs mutabilidade, `freeze`, `seal`, `preventExtensions` |
| **Semana 5** | 8.5 — Herança por Protótipo | Cadeia com `Object.create`, property shadowing, `Object.hasOwn()`, composição/mixins |
| **Semana 6** | 8.6 — Funções Construtoras | `new`, `.prototype`, `instanceof`, `class`, `extends`, `super`, `#private fields`, static init blocks |

Cada semana tem **4 períodos de 45 min** (3h/aula).

---

## Semana 1 — 8.1 Orientação a Objetos Prototípica

### Distribuição dos períodos

| Período | Duração | Atividade |
|---|---|---|
| **1º** | 45 min | **Teoria + live coding**: Contraste OO clássica vs. prototípica. `typeof {}`, `Object.getPrototypeOf()`, `.__proto__`. |
| **2º** | 45 min | **Cadeia de protótipos**: `Object.prototype` como raiz, `Array.prototype`, `Function.prototype`. |
| **3º** | 45 min | **Delegação**: `Object.create()`, propriedades herdadas, `hasOwnProperty()`. |
| **4º** | 45 min | **Exercícios práticos** + revisão.

---

### 8.1 Orientação a Objetos Prototípica

#### Conceitos a demonstrar

| Conceito | O que mostrar |
|---|---|
| Todo objeto tem um protótipo | `typeof {}`, `Object.getPrototypeOf({})`, `{}.__proto__` **(nota didática: `__proto__` é obsoleto; usar `Object.getPrototypeOf()` em código real)** |
| Cadeia de protótipos (prototype chain) | `Object.prototype` como raiz da cadeia. `Array.prototype`, `Function.prototype` também são objetos ligados em cadeia |
| Contraste com OO clássica | Em Java/PHP: classes são "moldes", objetos são "instâncias". Em JS: objetos delegam para outros objetos via `[[Prototype]]` |
| `Object.hasOwn()` (ES2022) | Substituto moderno e mais seguro para `obj.hasOwnProperty()` — evita problemas de shadowing |

#### Exemplo de demonstração interativa (console)

```javascript
// oo-prototypal.js

// 1. Todo objeto literal herda de Object.prototype
const product = {
    id: 1,
    name: "Teclado Mecânico",
    price: 299.90
};

console.log(typeof product);                        // "object"
console.log(Object.getPrototypeOf(product) === Object.prototype); // true
console.log(product.__proto__ === Object.prototype); // true

// 2. A cadeia de protótipos
console.log(Object.getPrototypeOf(Object.prototype)); // null — fim da cadeia

// 3. Objetos especializados também seguem a mesma lógica
const ids = [1, 2, 3];
console.log(Object.getPrototypeOf(ids) === Array.prototype);     // true
console.log(Object.getPrototypeOf(Array.prototype) === Object.prototype); // true

// 4. Até funções são objetos com protótipo
function sum(a, b) { return a + b; }
console.log(Object.getPrototypeOf(sum) === Function.prototype);  // true

// 5. Delegação de propriedades ao protótipo
const baseProduct = {
    getFormattedPrice() {
        return `R$ ${this.price.toFixed(2)}`;
    }
};

const mouse = Object.create(baseProduct);
mouse.id = 2;
mouse.name = "Mouse Gamer";
mouse.price = 189.90;

console.log(mouse.getFormattedPrice()); // "R$ 189.90" — herdado do protótipo
console.log(mouse.hasOwnProperty("name"));          // true  — próprio
console.log(mouse.hasOwnProperty("getFormattedPrice")); // false — está no protótipo
```

### 🏋️ Exercícios — Semana 1

**1. Criação de objeto protótipo**  
Crie um objeto `person` (pessoa) com propriedades `name` (nome) e `age` (idade), e um método `introduce()` (apresentar) que retorna `"Olá, me chamo [nome] e tenho [idade] anos"`. Depois, crie três objetos que herdam de `person` usando `Object.create()`.

**2. Cadeia de protótipos**  
Crie uma cadeia de herança: `Vehicle` (veículo) → `Car` (carro) → `myCar` (meu carro). Cada nível deve adicionar propriedades ou métodos. Use `Object.getPrototypeOf()` para verificar a cadeia.

**3. Property shadowing (sombreamento)**  
Crie um objeto `student` (aluno) com protótipo contendo método `calculateAverage()` (calcular média). No objeto filho, adicione uma propriedade `grade` (nota) que sombreie uma possível `grade` do protótipo. Use `hasOwnProperty()` para comprovar.

**4. Modificação em runtime**  
Crie um objeto `library` (biblioteca) e dois objetos `book` (livro) herdando dele. Adicione um método `renewCatalog()` (renovar catálogo) ao protótipo **depois** de criar os livros. Demonstre que ambos os livros passam a ter o método.

---

## Semana 2 — 8.2 Fundamentos de Criação de Objetos

#### Conceitos a demonstrar

| Forma | Sintaxe | Quando usar |
|---|---|---|
| Objeto literal | `const obj = { key: value }` | Objetos simples, configuração, dados pontuais |
| `Object.create(proto)` | `const obj = Object.create(base)` | Quando se quer definir explicitamente o protótipo |
| Construtor `new Object()` | `const obj = new Object()` | Raro. Prefira literais |
| `Object.assign(target, ...sources)` | `Object.assign({}, a, b)` | Mesclar propriedades de múltiplos objetos |
| `Object.defineProperty()` | Configuração fina de propriedades (writable, enumerable, configurable) |

#### Exemplo de demonstração

```javascript
// creation-objects.js

// 1. Objeto literal
const product = {
    id: 1,
    name: "Monitor 24\"",
    price: 899.90,
    active: true,
    getInfo() {
        return `${this.name} — R$ ${this.price.toFixed(2)}`;
    }
};

console.log(product.getInfo());

// 2. Object.create() — criando com protótipo explícito
const productMethods = {
    getFormattedPrice() {
        return `R$ ${this.price.toFixed(2).replace(".", ",")}`;
    },
    activate() {
        this.active = true;
        return this;
    },
    deactivate() {
        this.active = false;
        return this;
    }
};

const keyboard = Object.create(productMethods);
keyboard.id = 2;
keyboard.name = "Teclado Mecânico RGB";
keyboard.price = 459.90;
keyboard.active = true;

console.log(keyboard.getFormattedPrice()); // "R$ 459,90"
console.log(keyboard.deactivate().active); // false

// 3. Object.assign() — mesclando objetos
const dimensions = { width: 45, height: 30, depth: 5 };
const stock = { quantity: 120, warehouse: "A1" };
const fullProduct = Object.assign({}, product, dimensions, stock);
console.log(fullProduct);

// 4. Object.defineProperty() — controle fino de propriedades
const user = {};
Object.defineProperty(user, "email", {
    value: "aluno@ifsul.edu.br",
    writable: false,      // não pode ser alterado
    enumerable: true,     // aparece em for..in e Object.keys()
    configurable: false   // não pode ser deletado nem reconfigurado
});
```

---

## Semana 2 — 8.2 Fundamentos de Criação de Objetos

### Distribuição dos períodos

| Período | Duração | Atividade |
|---|---|---|
| **1º** | 45 min | Objeto literal, `Object.create()` com protótipo explícito |
| **2º** | 45 min | `Object.assign()` — mesclagem de objetos |
| **3º** | 45 min | `Object.defineProperty()` — descritores `writable`, `enumerable`, `configurable` |
| **4º** | 45 min | **Exercícios práticos** |

### 🏋️ Exercícios — Semana 2

**1. Comparação de técnicas**  
Crie o mesmo objeto `product` (produto) com propriedades `name` (nome), `price` (preço), `category` (categoria) usando as 4 formas: literal, `new Object()`, factory function e `Object.create()`. Compare sintaxe e facilidade de reutilização.

**2. Factory function reutilizável**  
Implemente uma factory `createUser(name, email, role)` (criar usuário - nome, email, perfil) que retorna objetos com método `displayData()` (exibir dados). Crie 3 usuários diferentes e invoque o método de cada um.

**3. Getters e setters avançados**  
Crie um objeto `account` (conta) com `balance` (saldo) privado (usando closure ou WeakMap). Adicione getter `balance` e métodos `deposit(amount)` (depositar - valor) e `withdraw(amount)` (sacar - valor). O saldo nunca pode ser negativo.

**4. Configuração de propriedades**  
Use `Object.defineProperty()` para criar um objeto `config` (configuração) onde:
- `version` (versão) é somente leitura (`writable: false`)
- `apiKey` (chave API) não aparece em `for...in` (`enumerable: false`)
- `mode` (modo) não pode ser deletado (`configurable: false`)

---

## Semana 3 — 8.3 Clonagem de Objetos

### Distribuição dos períodos

| Período | Duração | Atividade |
|---|---|---|
| **1º** | 45 min | Shallow copy com `{...spread}` e `Object.assign()` |
| **2º** | 45 min | Problema do compartilhamento de referências aninhadas |
| **3º** | 45 min | Deep copy com `structuredClone()` e `JSON.parse(JSON.stringify())` |
| **4º** | 45 min | **Exercícios práticos** — comparar técnicas |

---

### 8.3 Clonagem de Objetos

#### Conceitos a demonstrar

| Tipo | Técnica | Comportamento |
|---|---|---|
| **Shallow copy** | `{...obj}`, `Object.assign({}, obj)` | Copia apenas referências de 1º nível. Objetos aninhados são compartilhados |
| **Deep copy** | `structuredClone(obj)`, `JSON.parse(JSON.stringify(obj))` | Cópia recursiva. Objetos aninhados são independentes |
| **Limitações do JSON** | `JSON.parse(JSON.stringify(obj))` | Não copia funções, `undefined`, `Date` (vira string), `Map`, `Set`, referências circulares |

#### Exemplo de demonstração

```javascript
// cloning-objects.js

// Objeto com aninhamento — domínio: carrinho de compras
const cart = {
    id: 503,
    customer: "Maria Silva",
    items: [
        { productId: 1, name: "Mouse", price: 89.90, quantity: 2 },
        { productId: 2, name: "Teclado", price: 259.90, quantity: 1 }
    ],
    getTotal() {
        return this.items.reduce((total, item) => total + item.price * item.quantity, 0);
    }
};

// ----- SHALLOW COPY (cópia rasa) -----
const shallowCart = { ...cart };

// Alterando o array aninhado AFETA o original (mesma referência!)
shallowCart.items[0].quantity = 99;                  // Parece inofensivo...
console.log(cart.items[0].quantity);                  // 99 — o original foi alterado!
shallowCart.items.push({ productId: 3, name: "Fone", price: 150, quantity: 1 });
console.log(cart.items.length);                       // 3 — o original também ganhou o item!

// ----- DEEP COPY (cópia profunda) -----

// Método 1: structuredClone() (moderno, recomendado)
const deepCart = structuredClone(cart);

// Atenção: structuredClone NÃO copia funções
console.log(typeof deepCart.getTotal); // "undefined" — funções são descartadas

deepCart.items[0].quantity = 5;                       // Altera apenas a cópia
console.log(cart.items[0].quantity);                   // 99 — original preservado ✓

// Método 2: JSON.parse(JSON.stringify()) (clássico, com limitações)
const jsonCart = JSON.parse(JSON.stringify(cart));
jsonCart.items[0].quantity = 10;
console.log(cart.items[0].quantity);                   // 99 — original preservado ✓

// ----- COMPARAÇÃO VISUAL -----
console.log("Original items:", cart.items);
console.log("Shallow items:", shallowCart.items);      // mesmo array do original!
console.log("Deep items:   ", deepCart.items);         // array independente
```

### 🏋️ Exercícios — Semana 3

**1. Shallow vs. Deep copy na prática**  
Crie um objeto `person` (pessoa) com propriedades simples `name` (nome), `age` (idade) e aninhadas `address` (endereço): `{street (rua), city (cidade)}`. Faça duas cópias: uma com `{...spread}` e outra com `structuredClone()`. Modifique `address.city` em cada cópia e observe o comportamento.

**2. Problema do JSON.parse/stringify**  
Crie um objeto `user` (usuário) com:
- Uma data `birthdate` (data de nascimento) (`new Date()`)
- Uma função `calculateAge()` (calcular idade)
- Um valor `undefined`
Clone usando `JSON.parse(JSON.stringify())` e identifique o que foi perdido.

**3. Tabela comparativa**  
Implemente os 3 métodos de clonagem (spread, structuredClone, JSON) em objetos com diferentes características (aninhados, com funções, com Dates). Monte uma tabela mostrando o que cada técnica preserva.

**4. Clonagem seletiva**  
Crie uma função `cloneWithoutSensitive(object)` (clonar sem dados sensíveis - objeto) que faça deep copy de um objeto, mas **remove** propriedades como `password` (senha), `ssn` (cpf), `creditCard` (cartão de crédito) antes de retornar a cópia.

---

## Semana 4 — 8.4 Mutabilidade de Objetos

### Distribuição dos períodos

| Período | Duração | Atividade |
|---|---|---|
| **1º** | 45 min | `const` vs. mutabilidade — quebrar o mito |
| **2º** | 45 min | `Object.freeze()` e `Object.isFrozen()` |
| **3º** | 45 min | `Object.seal()` e `Object.preventExtensions()` |
| **4º** | 45 min | **Exercícios práticos** — quadro comparativo |

---

### 8.4 Mutabilidade de Objetos

#### Conceitos a demonstrar

| Método | O que permite | O que bloqueia |
|---|---|---|
| `Object.freeze(obj)` | Apenas leitura | Adicionar, remover **ou alterar** propriedades |
| `Object.seal(obj)` | Alterar valores existentes | Adicionar ou remover propriedades |
| `Object.preventExtensions(obj)` | Alterar e remover existentes | Apenas **adicionar** novas propriedades |
| `Object.isFrozen(obj)` | — | Verifica se está congelado |
| `Object.isSealed(obj)` | — | Verifica se está selado |
| `Object.isExtensible(obj)` | — | Verifica se aceita novas propriedades |

#### Exemplo de demonstração

```javascript
// mutability-objects.js

// IMPORTANTE: const vs. mutabilidade
// const impede REATRIBUIR a variável, mas NÃO impede alterar as propriedades do objeto
const config = {
    appName: "ACME 3AM",
    version: "1.0.0",
    maxUploadSize: 5,
    features: ["dark-mode", "notifications"]
};

config.version = "1.1.0";         // Permitido — altera propriedade
config.newField = "test";         // Permitido — adiciona propriedade
delete config.maxUploadSize;      // Permitido — remove propriedade
// config = {};                   // ERRO! — reatribuição bloqueada pelo const

console.log(config);

// ----- Object.freeze() -----
const FROZEN_CONFIG = Object.freeze({
    apiUrl: "http://localhost:8080",
    timeout: 30000,
    retries: 3
});

// FROZEN_CONFIG.timeout = 5000;  // ERRO (strict mode) ou silencioso (não-strict)
// FROZEN_CONFIG.newKey = "val";  // ERRO — não pode adicionar
// delete FROZEN_CONFIG.retries;  // ERRO — não pode remover

console.log(Object.isFrozen(FROZEN_CONFIG)); // true
console.log(FROZEN_CONFIG.timeout);          // 30000 — valor original preservado

// ----- Object.seal() -----
const sealedProduct = Object.seal({
    id: 1,
    name: "Cadeira Gamer",
    price: 1299.90
});

sealedProduct.price = 999.90;     // Permitido — altera valor existente
// sealedProduct.stock = 50;      // ERRO — não pode adicionar
// delete sealedProduct.name;     // ERRO — não pode remover

console.log(Object.isSealed(sealedProduct));  // true
console.log(Object.isExtensible(sealedProduct)); // false

// ----- Object.preventExtensions() -----
const restricted = Object.preventExtensions({
    id: 1,
    label: "Categoria"
});

restricted.label = "Nova Categoria"; // Permitido — altera existente
delete restricted.id;                 // Permitido — remove existente
// restricted.nova = "teste";         // ERRO — não pode adicionar

console.log(Object.isExtensible(restricted)); // false

// ----- QUADRO RESUMO -----
console.table({
    "Normal":          { add: "✓", delete: "✓", modify: "✓" },
    "preventExtensions": { add: "✗", delete: "✓", modify: "✓" },
    "seal":            { add: "✗", delete: "✗", modify: "✓" },
    "freeze":          { add: "✗", delete: "✗", modify: "✗" }
});
```

### 🏋️ Exercícios — Semana 4

**1. Quebrar o mito do const**  
Crie um objeto `const user = {name: "Ana"}` (usuário - nome). Modifique propriedades, adicione novas, delete existentes. Explique por que `const` não impede mutação do objeto.

**2. Níveis de proteção**  
Crie um objeto `config` (configuração) e aplique os 3 níveis: `Object.freeze()`, `Object.seal()`, `Object.preventExtensions()`. Monte uma tabela comparando o que cada um permite/bloqueia.

**3. Deep freeze recursivo**  
Implemente uma função `deepFreeze(object)` (congelar profundamente - objeto) que congela o objeto e **todos os seus objetos aninhados** recursivamente. Teste com estrutura de 3 níveis.

**4. Sistema de permissões**  
Crie objetos representando diferentes perfis de usuário: `admin` (administrador), `editor` (editor), `reader` (leitor). Use freeze/seal para garantir que:
- Admin pode tudo
- Editor pode modificar, mas não adicionar propriedades (seal)
- Leitor é read-only (freeze)

---

## Semana 5 — 8.5 Herança por Meio de Protótipo

### Distribuição dos períodos

| Período | Duração | Atividade |
|---|---|---|
| **1º** | 45 min | Cadeia de protótipos com `Object.create()` — Product → DigitalProduct → OnlineCourse |
| **2º** | 45 min | `Object.getPrototypeOf()`, `Object.setPrototypeOf()` |
| **3º** | 45 min | Property shadowing e `hasOwnProperty()` |
| **4º** | 45 min | **Exercícios práticos** — estender a cadeia |

---

### 8.5 Herança por Meio de Protótipo

#### Conceitos a demonstrar

| Conceito | Explicação |
|---|---|
| Cadeia de protótipos via `Object.create()` | Cada objeto aponta para um protótipo; propriedades não encontradas são buscadas "subindo" na cadeia |
| `Object.setPrototypeOf(obj, proto)` | Altera o protótipo de um objeto existente (custo de performance — evitar em loops) |
| `Object.getPrototypeOf(obj)` | Obtém o protótipo atual de um objeto |
| Sombra de propriedade (property shadowing) | Se o objeto possui uma propriedade com o mesmo nome do protótipo, a do objeto "sombreia" a do protótipo |
| `Object.hasOwn(obj, prop)` (ES2022) | Substituto moderno de `hasOwnProperty()` — verifica se a propriedade pertence ao próprio objeto |
| **Composição / Mixins** | Alternativa à herança profunda: compor objetos com `Object.assign()` combinando múltiplos comportamentos (ex: `canLog`, `canSerialize`, `canDiscount`). Mais flexível e menos acoplado que cadeias longas de herança |

#### Exemplo de demonstração

```javascript
// inheritance-prototype.js

// ----- Cadeia de protótipos no domínio do projeto -----
// Modelo: Product → DigitalProduct → OnlineCourse

// Nível 1 — "Classe base": Product
const Product = {
    init(id, name, price) {
        this.id = id;
        this.name = name;
        this.price = price;
        this.active = true;
        return this;
    },
    getInfo() {
        return `${this.name} — R$ ${this.price.toFixed(2)}`;
    },
    deactivate() {
        this.active = false;
        return this;
    }
};

// Nível 2 — "Herda" de Product: DigitalProduct
const DigitalProduct = Object.create(Product);
DigitalProduct.init = function (id, name, price, fileSizeMB) {
    // Chama o init do "pai" via prototype chain
    Product.init.call(this, id, name, price);
    this.fileSizeMB = fileSizeMB;
    this.downloadable = true;
    return this;
};
DigitalProduct.getDownloadInfo = function () {
    return `${this.name} (${this.fileSizeMB} MB) — Download imediato`;
};

// Nível 3 — "Herda" de DigitalProduct: OnlineCourse
const OnlineCourse = Object.create(DigitalProduct);
OnlineCourse.init = function (id, name, price, fileSizeMB, instructor, durationHours) {
    DigitalProduct.init.call(this, id, name, price, fileSizeMB);
    this.instructor = instructor;
    this.durationHours = durationHours;
    this.studentsEnrolled = 0;
    return this;
};
OnlineCourse.enroll = function () {
    this.studentsEnrolled++;
    console.log(`Aluno matriculado! Total: ${this.studentsEnrolled}`);
};

// ----- USO -----
const jsCourse = Object.create(OnlineCourse).init(
    101,
    "JavaScript Avançado",
    199.90,
    4500,
    "Prof. Rafael",
    40
);

console.log(jsCourse.getInfo());            // "JavaScript Avançado — R$ 199.90" (herdado de Product)
console.log(jsCourse.getDownloadInfo());    // "... (4500 MB) — Download imediato" (herdado de DigitalProduct)
console.log(jsCourse.instructor);           // "Prof. Rafael" (próprio)
jsCourse.enroll();                          // "Aluno matriculado! Total: 1" (próprio)

// ----- Verificando a cadeia (prototype chain) -----
console.log(Object.getPrototypeOf(jsCourse) === OnlineCourse);       // true
console.log(Object.getPrototypeOf(OnlineCourse) === DigitalProduct); // true
console.log(Object.getPrototypeOf(DigitalProduct) === Product);      // true
console.log(Object.getPrototypeOf(Product) === Object.prototype);    // true
console.log(Object.getPrototypeOf(Object.prototype));                // null — fim da cadeia

// ----- Property shadowing (sombreamento) -----
const item = Object.create(Product);
item.name = "Nome local"; // sombreia qualquer name que existisse no protótipo
console.log(item.hasOwnProperty("name")); // true
console.log(item.hasOwnProperty("getInfo")); // false — está no protótipo
```

### 🏋️ Exercícios — Semana 5

**1. Cadeia de herança prototípica**  
Crie uma cadeia: `Animal` (animal) → `Mammal` (mamífero) → `Dog` (cachorro) → `myPet` (meu pet). Cada nível adiciona propriedades e métodos. Use `Object.getPrototypeOf()` e `isPrototypeOf()` para navegar/validar a cadeia.

**2. Property shadowing intencional**  
Crie um objeto `employee` (funcionário) que herda de `person` (pessoa). Adicione um método `introduce()` (apresentar) no filho que **complementa** (não substitui) o do pai. Use `Object.getPrototypeOf(this).introduce()` para chamar o método pai.

**3. Modificação dinâmica do protótipo**  
Crie vários objetos herdando de um protótipo `Vehicle` (veículo). Adicione um método `start()` (iniciar) ao protótipo **depois** que os objetos foram criados. Demonstre que todos passam a ter o método imediatamente.

**4. Verificação de herança**  
Implemente uma função `isVehicleType(object)` (é tipo de veículo - objeto) que retorna `true` se o objeto (direta ou indiretamente) herda de um protótipo `Vehicle` (veículo). Use `isPrototypeOf()` ou `Object.getPrototypeOf()`.

---

## Semana 6 — 8.6 Funções Construtoras

### Distribuição dos períodos

| Período | Duração | Atividade |
|---|---|---|
| **1º** | 45 min | Função construtora com `new`, `.prototype`, `instanceof` |
| **2º** | 45 min | Herança com `call()` + `Object.create()` |
| **3º** | 45 min | `class` como syntactic sugar; `extends` e `super()`; `#private fields` (ES2022) e static init blocks |
| **4º** | 45 min | **Exercícios práticos** + revisão geral da unidade |

---

### 8.6 Funções Construtoras

#### Conceitos a demonstrar

| Conceito | Explicação |
|---|---|
| Função construtora (pre-ES6) | Função comum invocada com `new`. Cria `this` → liga protótipo → retorna `this` |
| `Constructor.prototype` | Todo construtor tem `.prototype` — este será o `[[Prototype]]` das instâncias |
| `instanceof` | Verifica se um objeto está na cadeia de protótipos de um construtor |
| `class` (ES6) | Syntactic sugar sobre funções construtoras. Por baixo, ainda é prototípico |
| `#private fields` (ES2022) | Campos verdadeiramente privados com prefixo `#` — não acessíveis fora da classe |
| `static` initialization blocks (ES2022) | Blocos `static { ... }` para inicialização complexa de membros estáticos |
| Comparação lado a lado | Mostrar a mesma "classe" escrita como função construtora e como `class` |

#### Exemplo de demonstração

```javascript
// constructor-functions.js

// ==========================================
// VERSÃO 1: Função construtora (estilo ES5)
// ==========================================

function ProductConstructor(id, name, price) {
    // Quando chamada com 'new':
    // 1. Cria um objeto vazio: const this = {};
    // 2. Liga o protótipo: this.__proto__ = ProductConstructor.prototype;
    // 3. Executa o código abaixo
    // 4. Retorna this automaticamente

    this.id = id;
    this.name = name;
    this.price = price;
    this.active = true;

    // NÃO retornar nada explicitamente — 'new' cuida disso
}

// Métodos vão no .prototype (compartilhados entre todas as instâncias)
ProductConstructor.prototype.getInfo = function () {
    return `${this.name} — R$ ${this.price.toFixed(2)}`;
};

ProductConstructor.prototype.deactivate = function () {
    this.active = false;
    return this;
};

// Método estático (direto no construtor, não no prototype)
ProductConstructor.comparePrices = function (a, b) {
    return a.price - b.price;
};

// ----- USO -----
const p1 = new ProductConstructor(1, "Mouse", 89.90);
const p2 = new ProductConstructor(2, "Teclado", 259.90);

console.log(p1.getInfo());                           // "Mouse — R$ 89.90"
console.log(p2.getInfo());                           // "Teclado — R$ 259.90"

// Verificando a identidade prototípica
console.log(p1 instanceof ProductConstructor);       // true
console.log(Object.getPrototypeOf(p1) === ProductConstructor.prototype); // true

// Os métodos são compartilhados (mesma referência na memória)
console.log(p1.getInfo === p2.getInfo);              // true — mesmo método!

// ==========================================
// VERSÃO 2: class (ES6) — syntactic sugar
// ==========================================

class ProductClass {
    constructor(id, name, price) {
        this.id = id;
        this.name = name;
        this.price = price;
        this.active = true;
    }

    getInfo() {
        return `${this.name} — R$ ${this.price.toFixed(2)}`;
    }

    deactivate() {
        this.active = false;
        return this;
    }

    static comparePrices(a, b) {
        return a.price - b.price;
    }
}

// USO idêntico
const p3 = new ProductClass(3, "Monitor", 899.90);
console.log(p3.getInfo());                           // "Monitor — R$ 899.90"
console.log(p3 instanceof ProductClass);             // true

// PROVA de que class é syntax sugar — por baixo ainda é função + prototype!
console.log(typeof ProductClass);                    // "function"
console.log(typeof ProductConstructor);              // "function"
console.log(Object.getPrototypeOf(p3) === ProductClass.prototype); // true

// ==========================================
// Herança com funções construtoras
// ==========================================

function DigitalProductConstructor(id, name, price, fileSizeMB) {
    // "Chamar super" — executa o construtor pai no contexto do novo objeto
    ProductConstructor.call(this, id, name, price);
    this.fileSizeMB = fileSizeMB;
    this.downloadable = true;
}

// Estabelece a cadeia de protótipos: DigitalProductConstructor.prototype → ProductConstructor.prototype
DigitalProductConstructor.prototype = Object.create(ProductConstructor.prototype);
DigitalProductConstructor.prototype.constructor = DigitalProductConstructor; // corrige a referência

DigitalProductConstructor.prototype.getDownloadInfo = function () {
    return `${this.name} (${this.fileSizeMB} MB) — Download imediato`;
};

const digital = new DigitalProductConstructor(10, "Curso de PHP", 99.90, 1200);
console.log(digital.getInfo());           // herdado de ProductConstructor
console.log(digital.getDownloadInfo());   // próprio
console.log(digital instanceof DigitalProductConstructor);  // true
console.log(digital instanceof ProductConstructor);         // true (herança!)

// ==========================================
// Herança com class (equivalente)
// ==========================================

class DigitalProductClass extends ProductClass {
    constructor(id, name, price, fileSizeMB) {
        super(id, name, price);
        this.fileSizeMB = fileSizeMB;
        this.downloadable = true;
    }

    getDownloadInfo() {
        return `${this.name} (${this.fileSizeMB} MB) — Download imediato`;
    }
}

const digital2 = new DigitalProductClass(11, "Curso de JS", 199.90, 4500);
console.log(digital2.getInfo());          // herdado
console.log(digital2.getDownloadInfo());  // próprio
```

### 🏋️ Exercícios — Semana 6

**1. Função construtora básica**  
Crie uma função `Book(title, author, year)` (livro - título, autor, ano) que usa `this`. Instancie 3 livros com `new`. Adicione um método `displayInfo()` (exibir info) no `.prototype` e demonstre que todos os livros têm acesso.

**2. Herança com call() e Object.create()**  
Implemente herança entre `Person` (pessoa) com `name` (nome), `age` (idade) e `Student` (aluno) adicionando `course` (curso). Use `Person.call(this, ...)` no construtor de `Student` e configure `Student.prototype = Object.create(Person.prototype)`.

**3. Class vs. Função construtora**  
Implemente a mesma hierarquia `Vehicle` (veículo) → `Car` (carro) de duas formas:
- Com funções construtoras + .prototype
- Com `class` + `extends` + `super()`
Compare a sintaxe e funcionalidade resultante.

**4. Projeto integrador — Sistema de produtos**  
Crie uma hierarquia:
- `Product` (produto) com `id`, `name` (nome), `price` (preço) e método `calculateDiscount(percentage)` (calcular desconto - percentual)
- `PhysicalProduct` (produto físico) adiciona `weight` (peso), `calculateShipping()` (calcular frete)
- `DigitalProduct` (produto digital) adiciona `fileSizeMB` (tamanho em MB), `getDownloadLink()` (obter link de download)
Use `class` e implemente 2 instâncias de cada tipo. Demonstre herança e polimorfismo.

---

## Resumo do cronograma

| Semana | 1º período (45 min) | 2º período (45 min) | 3º período (45 min) | 4º período (45 min) |
|---|---|---|---|---|
| **Semana 1** | 8.1 — Teoria OO prototípica | Delegação e cadeia | Object.create() e herança | **Exercícios práticos** |
| **Semana 2** | 8.2 — Formas de criar objetos | Factory functions | Getters/setters | **Exercícios práticos** |
| **Semana 3** | 8.3 — Shallow copy (spread, assign) | Problema de referências aninhadas | Deep copy (structuredClone, JSON) | **Exercícios práticos** |
| **Semana 4** | 8.4 — const vs. mutabilidade | Object.freeze() | Object.seal() e preventExtensions() | **Exercícios práticos** |
| **Semana 5** | 8.5 — Object.create() herança | Cadeia de protótipos + Object.hasOwn() | Composição/Mixins (alternativa à herança) | **Exercícios práticos** |
| **Semana 6** | 8.6 — Funções construtoras | Herança (call + Object.create) | class, extends, super + #private fields | **Exercícios + revisão geral** |

---

## Checklist de preparação

### Arquivos JavaScript (em `assets/scripts/`)
- [x] `oo-prototypal.js` — 8.1 OO Prototípica (+ nota sobre `__proto__` deprecated, `Object.hasOwn()`)
- [x] `creation-objects.js` — 8.2 Criação de Objetos (+ exercícios como comentários)
- [x] `cloning-objects.js` — 8.3 Clonagem de Objetos (+ feature detection p/ `structuredClone()`)
- [x] `mutability-objects.js` — 8.4 Mutabilidade de Objetos (+ exercícios como comentários)
- [x] `inheritance-prototype.js` — 8.5 Herança por Protótipo (+ `Object.hasOwn()`, composição/mixins)
- [x] `constructor-functions.js` — 8.6 Funções Construtoras (+ `#private fields` ES2022, static init blocks)

### Arquivos HTML (na raiz do projeto)
- [x] `oo-prototypal.html` — tela para semana 1
- [x] `creation-objects.html` — tela para semana 2
- [x] `cloning-objects.html` — tela para semana 3
- [x] `mutability-objects.html` — tela para semana 4
- [x] `inheritance-prototype.html` — tela para semana 5
- [x] `constructor-functions.html` — tela para semana 6

### Material didático
- [ ] Slides ou roteiro para cada semana (1-6)
- [x] Listas de exercícios integradas ao plano (4 exercícios por semana + exercícios extras nos .js)
- [x] Gabaritos comentados dos exercícios (`plans/gabarito-exercicios.js` — gitignored para alunos)
- [x] Projeto integrador final (Semana 6 - exercício 4)

### Recursos modernos adicionados (ES2022+)
- [x] `#private fields` e `#private methods` — Semana 6 (`constructor-functions.js`)
- [x] `static` initialization blocks — Semana 6 (`constructor-functions.js`)
- [x] `Object.hasOwn()` como substituto de `hasOwnProperty()` — Semanas 1 e 5
- [x] Composição/Mixins via `Object.assign()` — Semana 5 (`inheritance-prototype.js`)
- [x] CSS custom properties (`:root`) em `base.css`
- [x] Template literals padronizados em todos os scripts
- [x] `console.group()` / `console.groupEnd()` em todos os scripts
- [x] Nota sobre arrow functions e `this` léxico — Semanas 2 e 6
- [x] Optional chaining (`?.`) e nullish coalescing (`??`) — Semana 2
- [x] `Symbol` como propriedade semi-privada (contexto histórico pré-ES2022) — Semana 6

### Verificação técnica
- [ ] Testar todos os exemplos no console do navegador (Chrome/Firefox)
- [x] Feature detection para `structuredClone()` implementado (fallback amigável)
- [x] Verificar compatibilidade: `structuredClone()` disponível a partir do Chrome 98 / Firefox 94
