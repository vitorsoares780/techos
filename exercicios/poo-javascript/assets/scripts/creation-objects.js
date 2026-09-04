// =====================================================
// 8.2 — Fundamentos de Criação de Objetos
// (literal, Object.create, Object.assign, Object.defineProperty)
// =====================================================
// Domínio: produtos do sistema ACME
//
// NOTA SOBRE ARROW FUNCTIONS vs. FUNCTIONS:
// Arrow functions () => {} NÃO têm seu próprio `this` —
// capturam o `this` do escopo onde foram definidas (léxico).
// Por isso NÃO use arrow functions como métodos de objeto:
//
//   const obj = {
//     name: "Teste",
//     sayHi: () => console.log(this.name)  // ❌ ERRO: `this` é o escopo externo
//     sayHi() { console.log(this.name) }   // ✔ CORRETO: `this` é o próprio objeto
//   };
//
// Use function() {} ou método shorthand para métodos de objeto.
// Use arrow functions para callbacks curtos (map, filter, etc.).
// =====================================================

// 1. Objeto literal
console.group("1. Objeto literal");
const product = {
    id: 1,
    name: 'Monitor 24"',
    price: 899.90,
    active: true,
    getInfo() {
        return `${this.name} — R$ ${this.price.toFixed(2)}`;
    }
};

console.log(`product.getInfo(): ${product.getInfo()}`);
console.groupEnd();

// 2. Object.create() — criando com protótipo explícito
console.group("2. Object.create()");
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

console.log(`keyboard.getFormattedPrice(): ${keyboard.getFormattedPrice()}`); // "R$ 459,90"
console.log(`keyboard.deactivate().active: ${keyboard.deactivate().active}`);  // false
console.groupEnd();

// 3. Object.assign() — mesclando objetos
console.group("3. Object.assign()");
const dimensions = { width: 45, height: 30, depth: 5 };
const stock = { quantity: 120, warehouse: "A1" };
const fullProduct = Object.assign({}, product, dimensions, stock);
console.log("fullProduct:", fullProduct);
console.groupEnd();

// 4. Object.defineProperty() — controle fino de propriedades
console.group("4. Object.defineProperty()");
const user = {};
Object.defineProperty(user, "email", {
    value: "aluno@ifsul.edu.br",
    writable: false,      // não pode ser alterado
    enumerable: true,     // aparece em for..in e Object.keys()
    configurable: false   // não pode ser deletado nem reconfigurado
});

console.log(`email: ${user.email}`);
// user.email = "outro@email.com"; // não altera (silencioso ou erro em strict mode)
// delete user.email;               // não deleta
console.log(`Tentativa de alteração ignorada, email: ${user.email}`);
console.groupEnd();

// =====================================================
// OPTIONAL CHAINING (?.) e NULLISH COALESCING (??) — ES2020
// =====================================================

console.group("5. Optional chaining (?.) e nullish coalescing (??)");

const customer = {
    name: "João",
    address: {
        city: "Pelotas",
        street: "Rua A"
    }
    // zipCode não definido
};

// Optional chaining: acessa propriedades aninhadas com segurança
// Se qualquer nível for null/undefined, retorna undefined em vez de lançar erro
console.log(`Cidade: ${customer.address?.city}`);        // "Pelotas"
console.log(`Bairro: ${customer.address?.district}`);    // undefined (não existe, mas não quebra)
console.log(`CEP: ${customer.address?.zipCode ?? "não informado"}`); // "não informado"

// Nullish coalescing: retorna o lado direito apenas se o esquerdo for null ou undefined
// (diferente de || que também considera "", 0, false como falsy)

const config = {
    timeout: 0,            // 0 é um valor válido!
    retries: null,
    title: ""
};

console.log(`timeout: ${config.timeout ?? 5000}`);   // 0     (0 é válido, não cai no fallback)
console.log(`timeout (com ||): ${config.timeout || 5000}`); // 5000  (|| trata 0 como falsy — perigoso!)
console.log(`retries: ${config.retries ?? 3}`);       // 3     (null cai no fallback)
console.log(`title: ${config.title ?? "Sem título"}`);// ""    (string vazia é válida)
console.groupEnd();

// =====================================================
// 🏋️ EXERCÍCIOS — Semana 2
// =====================================================

// 1. Crie o mesmo objeto `product` (name, price, category) usando
//    as 4 formas: literal, new Object(), factory function e Object.create().
//    Compare sintaxe e facilidade de reutilização.

// 2. Implemente uma factory `createUser(name, email, role)` que retorna
//    objetos com método `displayData()`. Crie 3 usuários diferentes
//    e invoque o método de cada um.

// 3. Crie um objeto `account` com `balance` privado (usando closure ou WeakMap).
//    Adicione getter `balance` e métodos `deposit(amount)` e `withdraw(amount)`.
//    O saldo nunca pode ser negativo.

// 4. Use Object.defineProperty() para criar um objeto `config` onde:
//    - `version` é somente leitura (writable: false)
//    - `apiKey` não aparece em for...in (enumerable: false)
//    - `mode` não pode ser deletado (configurable: false)

// 5. (Extra) Refatore os dados do cliente para usar optional chaining e
//    nullish coalescing. Teste com objetos que possuem e que não possuem
//    as propriedades aninhadas.
