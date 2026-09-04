// =====================================================
// 8.6 — Funções Construtoras e class
// (new, prototype, instanceof, class, extends, super)
// =====================================================
// Domínio: Produtos do sistema ACME
//
// NOVIDADES ES2022+ nesta aula:
// - #private fields (campos verdadeiramente privados)
// - Static initialization blocks (blocos de inicialização estática)
//
// NOTA SOBRE ARROW FUNCTIONS EM CLASSES:
// Arrow functions como campos de classe (class fields) têm `this`
// automaticamente vinculado à instância — por isso são seguras:
//
//   class Example {
//     value = 10;
//     method = () => this.value;   // ✔ `this` é a instância (léxico do constructor)
//   }
//
// Mas em métodos normais (prototype), NÃO use arrow functions:
//
//   Example.prototype.bad = () => this.value; // ❌ `this` não é a instância!
// =====================================================

// ==========================================
// VERSÃO 1: Função construtora (estilo ES5)
// ==========================================

console.group("1. Função construtora (ES5)");

function ProductConstructor(id, name, price) {
    this.id = id;
    this.name = name;
    this.price = price;
    this.active = true;
}

ProductConstructor.prototype.getInfo = function () {
    return `${this.name} — R$ ${this.price.toFixed(2)}`;
};

ProductConstructor.prototype.deactivate = function () {
    this.active = false;
    return this;
};

ProductConstructor.comparePrices = function (a, b) {
    return a.price - b.price;
};

const p1 = new ProductConstructor(1, "Mouse", 89.90);
const p2 = new ProductConstructor(2, "Teclado", 259.90);

console.log(`p1.getInfo(): ${p1.getInfo()}`);
console.log(`p2.getInfo(): ${p2.getInfo()}`);
console.log(`p1 instanceof ProductConstructor? ${p1 instanceof ProductConstructor}`);
console.log(`Métodos compartilhados? ${p1.getInfo === p2.getInfo}`);
console.groupEnd();

// ==========================================
// VERSÃO 2: class (ES6) — syntactic sugar
// ==========================================

console.group("2. class (ES6) — syntactic sugar");

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

const p3 = new ProductClass(3, "Monitor", 899.90);
console.log(`p3.getInfo(): ${p3.getInfo()}`);
console.log(`p3 instanceof ProductClass? ${p3 instanceof ProductClass}`);

// PROVA de que class é syntax sugar — por baixo ainda é função + prototype!
console.log(`typeof ProductClass: ${typeof ProductClass}`);                    // "function"
console.log(`typeof ProductConstructor: ${typeof ProductConstructor}`);        // "function"
console.log(`p3.__proto__ === ProductClass.prototype? ${Object.getPrototypeOf(p3) === ProductClass.prototype}`);
console.groupEnd();

// ==========================================
// Herança com funções construtoras
// ==========================================

console.group("3. Herança com funções construtoras");

function DigitalProductConstructor(id, name, price, fileSizeMB) {
    ProductConstructor.call(this, id, name, price);
    this.fileSizeMB = fileSizeMB;
    this.downloadable = true;
}

DigitalProductConstructor.prototype = Object.create(ProductConstructor.prototype);
DigitalProductConstructor.prototype.constructor = DigitalProductConstructor;

DigitalProductConstructor.prototype.getDownloadInfo = function () {
    return `${this.name} (${this.fileSizeMB} MB) — Download imediato`;
};

const digital = new DigitalProductConstructor(10, "Curso de PHP", 99.90, 1200);
console.log(`getInfo(): ${digital.getInfo()}`);
console.log(`getDownloadInfo(): ${digital.getDownloadInfo()}`);
console.log(`instanceof DigitalProductConstructor? ${digital instanceof DigitalProductConstructor}`);
console.log(`instanceof ProductConstructor? ${digital instanceof ProductConstructor}`);
console.groupEnd();

// ==========================================
// Herança com class
// ==========================================

console.group("4. Herança com class");

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
console.log(`getInfo(): ${digital2.getInfo()}`);
console.log(`getDownloadInfo(): ${digital2.getDownloadInfo()}`);
console.groupEnd();

// ==========================================
// #PRIVATE FIELDS (ES2022)
// ==========================================

console.group("5. #private fields (ES2022)");

class Account {
    #balance = 0;

    static #minDeposit;
    static {
        this.#minDeposit = 10;
        console.log(`Static init: depósito mínimo = R$ ${this.#minDeposit}`);
    }

    constructor(owner, initialBalance = 0) {
        this.owner = owner;
        this.#balance = initialBalance;
    }

    deposit(amount) {
        if (amount <= 0) {
            console.log("Valor de depósito inválido.");
            return;
        }
        this.#balance += amount;
        this.#logTransaction("Depósito", amount);
        console.log(`Depósito de R$ ${amount.toFixed(2)}. Saldo: R$ ${this.#balance.toFixed(2)}`);
    }

    withdraw(amount) {
        if (amount > this.#balance) {
            console.log("Saldo insuficiente.");
            return;
        }
        this.#balance -= amount;
        this.#logTransaction("Saque", amount);
        console.log(`Saque de R$ ${amount.toFixed(2)}. Saldo: R$ ${this.#balance.toFixed(2)}`);
    }

    get balance() {
        return this.#balance;
    }

    #logTransaction(type, amount) {
        console.log(`[PRIVADO] ${type}: R$ ${amount.toFixed(2)}`);
    }
}

const conta = new Account("Maria", 500);
conta.deposit(200);
conta.withdraw(150);
console.log(`Saldo via getter: ${conta.balance}`);
console.log(`owner (público): ${conta.owner}`);

// conta.#balance = 1000;   // ❌ ERRO! Private field não acessível fora da classe
// conta.#logTransaction(); // ❌ ERRO! Private method não acessível fora da classe
console.groupEnd();

// ==========================================
// Symbol — Propriedade "semi-privada" (pré-ES2022)
// ==========================================

console.group("6. Symbol — semi-privacidade antes do # (contexto histórico)");

// Antes do #private fields (ES2022), usava-se Symbol para criar propriedades
// que não colidem e não aparecem em iterações normais (mas NÃO são verdadeiramente privadas)
const _price = Symbol("price");

class LegacyProduct {
    constructor(id, name, price) {
        this.id = id;
        this.name = name;
        this[_price] = price; // "escondida" — não acessível por acidente
    }

    getPrice() {
        return this[_price];
    }
}

const lp = new LegacyProduct(1, "Teclado", 250);
console.log(`getPrice(): ${lp.getPrice()}`);       // 250 — acesso controlado via getter
console.log(`lp.price: ${lp.price}`);               // undefined — não existe propriedade "price"
console.log(`keys: ${Object.keys(lp)}`);            // ["id", "name"] — Symbol não aparece
console.log(`ownKeys: ${Object.getOwnPropertySymbols(lp).length}`); // 1 — mas ainda é recuperável

// ⚠ Diferente de #private, Symbol NÃO é verdadeiramente privado.
//    Com Object.getOwnPropertySymbols() ainda é possível acessar.
//    #private fields (ES2022) são realmente inacessíveis de fora da classe.
console.groupEnd();

// =====================================================
// 🏋️ EXERCÍCIOS — Semana 6
// =====================================================

// 1. Crie uma função construtora `Book(title, author, year)`.
//    Instancie 3 livros com `new`. Adicione `displayInfo()` no .prototype
//    e demonstre que todos os livros têm acesso.

// 2. Implemente herança entre `Person` (name, age) e `Student` (+ course).
//    Use Person.call(this, ...) no construtor de Student e configure
//    Student.prototype = Object.create(Person.prototype).

// 3. Implemente a mesma hierarquia `Vehicle → Car` de duas formas:
//    - Com funções construtoras + .prototype
//    - Com `class` + `extends` + `super()`
//    Compare sintaxe e funcionalidade.

// 4. (Projeto integrador) Crie uma hierarquia:
//    - `Product` (id, name, price, calculateDiscount(percentage))
//    - `PhysicalProduct` (+ weight, calculateShipping())
//    - `DigitalProduct` (+ fileSizeMB, getDownloadLink())
//    Use `class`. Implemente 2 instâncias de cada tipo.

// 5. (Extra) Converta a hierarquia acima para usar #private fields:
//    torne `id` e `price` privados. Adicione getters públicos.

// 6. (Extra) Compare Symbol vs. #private: crie uma classe com ambos e
//    tente acessar cada um de fora. Qual é realmente privado?
