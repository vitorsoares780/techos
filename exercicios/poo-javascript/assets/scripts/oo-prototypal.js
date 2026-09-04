// =====================================================
// 8.1 — Orientação a Objetos Prototípica
// =====================================================
// Domínio: produtos do sistema ACME
//
// NOTA: __proto__ é mostrado aqui para fins didáticos,
// mas é considerado obsoleto. Em código real, prefira
// Object.getPrototypeOf() e Object.setPrototypeOf().
//
// Para verificar se uma propriedade é do próprio objeto,
// use Object.hasOwn(obj, prop) (ES2022) no lugar de
// obj.hasOwnProperty(prop) — mais seguro contra shadowing.
// =====================================================

// 1. Todo objeto literal herda de Object.prototype
{
    const product = {
        id: 1,
        name: "Teclado Mecânico",
        price: 299.90
    };

    console.group("1. Todo objeto herda de Object.prototype");
    console.log(`typeof product: ${typeof product}`);
    console.log(`Object.getPrototypeOf(product) === Object.prototype: ${Object.getPrototypeOf(product) === Object.prototype}`);
    console.log(`product.__proto__ === Object.prototype: ${product.__proto__ === Object.prototype}`);
    console.groupEnd();
}

// 2. A cadeia de protótipos
console.group("2. Cadeia de protótipos");
console.log(`Object.getPrototypeOf(Object.prototype): ${Object.getPrototypeOf(Object.prototype)}`); // null — fim da cadeia
console.groupEnd();

// 3. Objetos especializados também seguem a mesma lógica
{
    const ids = [1, 2, 3];

    console.group("3. Objetos especializados");
    console.log(`Object.getPrototypeOf(ids) === Array.prototype: ${Object.getPrototypeOf(ids) === Array.prototype}`);
    console.log(`Object.getPrototypeOf(Array.prototype) === Object.prototype: ${Object.getPrototypeOf(Array.prototype) === Object.prototype}`);
    console.groupEnd();
}

// 4. Até funções são objetos com protótipo
{
    function sum(a, b) { return a + b; }

    console.group("4. Funções são objetos");
    console.log(`Object.getPrototypeOf(sum) === Function.prototype: ${Object.getPrototypeOf(sum) === Function.prototype}`);
    console.groupEnd();
}

// 5. Delegação de propriedades ao protótipo
console.group("5. Delegação de propriedades");

const baseProduct = {
    getFormattedPrice() {
        return `R$ ${this.price.toFixed(2)}`;
    }
};

const mouse = Object.create(baseProduct);
mouse.id = 2;
mouse.name = "Mouse Gamer";
mouse.price = 189.90;

console.log(`mouse.getFormattedPrice(): ${mouse.getFormattedPrice()}`); // "R$ 189.90" — herdado do protótipo

// Verificando propriedades próprias vs. herdadas
console.log(`name é própria? (hasOwnProperty): ${mouse.hasOwnProperty("name")}`);
console.log(`getFormattedPrice é própria? (hasOwnProperty): ${mouse.hasOwnProperty("getFormattedPrice")}`);

// ES2022+: forma moderna e mais segura de verificar propriedade própria
console.log(`name é própria? (Object.hasOwn): ${Object.hasOwn(mouse, "name")}`);
console.log(`getFormattedPrice é própria? (Object.hasOwn): ${Object.hasOwn(mouse, "getFormattedPrice")}`);
console.groupEnd();

// =====================================================
// 🏋️ EXERCÍCIOS — Semana 1
// =====================================================

// 1. Crie um objeto `person` com `name`, `age` e método `introduce()`
//    que retorna "Olá, me chamo [nome] e tenho [idade] anos".
//    Depois crie 3 objetos herdando de `person` via Object.create().       

// 2. Crie uma cadeia: Vehicle → Car → myCar.
//    Cada nível adiciona propriedades/métodos.
//    Use Object.getPrototypeOf() para verificar a cadeia.

// 3. Crie um objeto `student` com protótipo contendo `calculateAverage()`.
//    No filho, adicione `grade` que sombreia `grade` do protótipo.
//    Use Object.hasOwn() para comprovar.

// 4. Crie `library` e dois `book` herdando dele.
//    Adicione `renewCatalog()` ao protótipo DEPOIS de criar os livros.
//    Demonstre que ambos passam a ter o método.
