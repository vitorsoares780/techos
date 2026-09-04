// =====================================================
// 8.3 — Clonagem de Objetos (Shallow Copy & Deep Copy)
// =====================================================
// Domínio: carrinho de compras ACME
//
// NOTA: structuredClone() requer Chrome 98+ / Firefox 94+
// O código abaixo inclui feature detection para navegadores antigos.
// =====================================================

// Objeto com aninhamento — domínio: carrinho de compras
// const cart = {
//     id: 503,
//     customer: "Maria Silva",
//     items: [
//         { productId: 1, name: "Mouse", price: 89.90, quantity: 2 },
//         { productId: 2, name: "Teclado", price: 259.90, quantity: 1 }
//     ],
//     getTotal() {
//         return this.items.reduce((total, item) => total + item.price * item.quantity, 0);
//     }
// };

// // ----- SHALLOW COPY (cópia rasa) -----
// console.group("Shallow Copy (cópia rasa)");
// const shallowCart = { ...cart };

// // Alterando o array aninhado AFETA o original (mesma referência!)
// shallowCart.items[0].quantity = 99;
// console.log(`cart.items[0].quantity (original): ${cart.items[0].quantity}`); // 99 — o original foi alterado!
// shallowCart.items.push({ productId: 3, name: "Fone", price: 150, quantity: 1 });
// console.log(`cart.items.length (original): ${cart.items.length}`); // 3 — o original também ganhou o item!
// console.groupEnd();

// // ----- DEEP COPY (cópia profunda) -----
// console.group("Deep Copy (cópia profunda)");

// // Feature detection para structuredClone
// if (typeof structuredClone === "function") {
//     console.log("✔ structuredClone() disponível — usando método moderno");

//     const deepCart = structuredClone(cart);

//     // Atenção: structuredClone NÃO copia funções
//     console.log(`Tipo de getTotal após structuredClone: ${typeof deepCart.getTotal}`); // "undefined"

//     deepCart.items[0].quantity = 5;
//     console.log(`cart.items[0].quantity (original): ${cart.items[0].quantity}`);     // 99 — preservado
//     console.log(`deepCart.items[0].quantity (cópia): ${deepCart.items[0].quantity}`); // 5 — independente
// } else {
//     console.warn("⚠ structuredClone() não disponível — use JSON.parse(JSON.stringify()) como fallback.");
// }

// console.groupEnd();

// // Método 2: JSON.parse(JSON.stringify()) (clássico, com limitações)
// console.group("JSON.parse(JSON.stringify())");
// const jsonCart = JSON.parse(JSON.stringify(cart));
// jsonCart.items[0].quantity = 10;
// console.log(`cart.items[0].quantity (original): ${cart.items[0].quantity}`);     // 99 — preservado
// console.log(`jsonCart.items[0].quantity (cópia): ${jsonCart.items[0].quantity}`); // 10 — independente
// console.groupEnd();

// // Limitações do JSON: funções, undefined, Date viram string
// console.group("Limitações do JSON");
// console.log(`getTotal foi perdido? ${typeof jsonCart.getTotal}`); // "undefined"
// console.groupEnd();

// // ----- COMPARAÇÃO VISUAL -----
// console.group("Comparação final");
// console.log("Original items:", cart.items);
// console.log("Shallow items:", shallowCart.items);      // mesmo array do original!
// console.log("JSON items:", jsonCart.items);             // array independente
// console.groupEnd();

// =====================================================
// 🏋️ EXERCÍCIOS — Semana 3
// =====================================================

// 1. Crie `person` com `name`, `age` e `address: {street, city}`.
//    Faça shallow copy ({...spread}) e deep copy (structuredClone).
//    Modifique `address.city` em cada e observe o comportamento.

const person = {
    name: 'Mateus',
    age: 18,
    address: {
        street: 'Barreto Leite',
        city: 'São Jeo'
    }
}

console.group("Shallow Copy (cópia rasa)");
const shallowPerson = { ...person };

// Alterando o array aninhado AFETA o original (mesma referência!)
shallowPerson.address.city = 'GC';
console.log(`person.address.city (original): ${person.address.city}`);

console.groupEnd();

// ----- DEEP COPY (cópia profunda) -----
console.group("Deep Copy (cópia profunda)");

// Feature detection para structuredClone
if (typeof structuredClone === "function") {
    console.log("✔ structuredClone() disponível — usando método moderno");

    const deepPerson = structuredClone(person);

    deepPerson.address.city = 'TF';
    console.log(`person.address.city (original): ${person.address.city}`);
    console.log(`deepPerson.address.city (cópia): ${deepPerson.address.city}`);
} else {
    console.warn("⚠ structuredClone() não disponível — use JSON.parse(JSON.stringify()) como fallback.");
}

console.groupEnd();


// 2. Crie `user` com birthdate (new Date()), calculateAge() e uma
//    propriedade `undefined`. Clone com JSON.parse(JSON.stringify())
//    e identifique o que foi perdido.

const user = {
    birthdate: new Date(),
    calculateAge(){
        return 'Idade calculada'
    },
    undefined: undefined
}

// Método 2: JSON.parse(JSON.stringify()) (clássico, com limitações)
console.group("JSON.parse(JSON.stringify())");

const jsonUser = JSON.parse(JSON.stringify(user));
console.log(`user.birthdate (original): ${user.birthdate}`); 
console.log(`jsonUser.birthdate (cópia): ${jsonUser.birthdate}`);

console.log(`user.calculateAge() (original): ${user.calculateAge()}`); 
console.log(`jsonUser.calculateAge() (cópia): ${typeof jsonUser.calculateAge}`);

console.log(`user.undefined (original): ${user.undefined}`); 
console.log(`jsonUser.undefined (cópia): ${jsonUser.undefined}`);
console.groupEnd();


// 3. Implemente os 3 métodos de clonagem (spread, structuredClone, JSON)
//    em objetos com diferentes características. Monte uma tabela comparativa.

const teste = {
    nome: "Mateus",
    idade: 18,
    endereco: {
        cidade: "São Gabriel"
    },
    numeros: [1, 2, 3],
    data: new Date(),
    funcao() {
        return "teste";
    },
    valorIndefinido: undefined
};

const testeSemFuncao = {
    nome: "Mateus",
    idade: 18,
    endereco: {
        cidade: "São Gabriel"
    },
    numeros: [1, 2, 3],
    data: new Date(),
    valorIndefinido: undefined
};

console.group("Ex 3 - Shallow");

const shallowTeste = {...teste};

shallowTeste.endereco.cidade = "Nova Iorque";
console.log(`Original: ${teste.endereco.cidade}`);
console.log(`Cópia: ${shallowTeste.endereco.cidade}`);
console.groupEnd();

console.group("Ex 3 - Deep");

if (typeof structuredClone === "function") {
    console.log("✔ structuredClone() disponível — usando método moderno");

    const deepTeste = structuredClone(testeSemFuncao);

    deepTeste.idade = 25;
    console.log(`Original: ${testeSemFuncao.idade}`);
    console.log(`Cópia: ${deepTeste.idade}`);
    
} else {
    console.warn("⚠ structuredClone() não disponível — use JSON.parse(JSON.stringify()) como fallback.");
}

console.groupEnd();

console.group("Ex 3 - JSON");

const jsonTeste = JSON.parse(JSON.stringify(teste));
console.log(`teste.nome (original): ${teste.nome}`); 
console.log(`jsonTeste.nome (cópia): ${jsonTeste.nome}`);

console.groupEnd();

// 4. Crie `cloneWithoutSensitive(object)` que faz deep copy mas REMOVE
//    propriedades como `password`, `ssn`, `creditCard` antes de retornar.