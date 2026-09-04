// =====================================================
// 8.4 — Mutabilidade de Objetos
// (freeze, seal, preventExtensions)
// =====================================================
// Domínio: configurações do sistema ACME
// =====================================================

// IMPORTANTE: const vs. mutabilidade
// const impede REATRIBUIR a variável, mas NÃO impede alterar as propriedades do objeto
console.group("const vs. mutabilidade");
const config = {
    appName: "ACME 3AM",
    version: "1.0.0",
    maxUploadSize: 5,
    features: ["dark-mode", "notifications"]
};

config.version = "1.1.0";         // Permitido — altera propriedade
config.newField = "test";         // Permitido — adiciona propriedade
delete config.maxUploadSize;      // Permitido — remove propriedade
// config = {};                    // ERRO! — reatribuição bloqueada pelo const

console.log("config após modificações:", config);
console.groupEnd();

// ----- Object.freeze() -----
console.group("Object.freeze()");
const FROZEN_CONFIG = Object.freeze({
    apiUrl: "http://localhost:8080",
    timeout: 30000,
    retries: 3
});

// FROZEN_CONFIG.timeout = 5000;  // ERRO (strict mode) ou silencioso (não-strict)
// FROZEN_CONFIG.newKey = "val";  // ERRO — não pode adicionar
// delete FROZEN_CONFIG.retries;  // ERRO — não pode remover

console.log(`Está congelado? ${Object.isFrozen(FROZEN_CONFIG)}`); // true
console.log(`timeout: ${FROZEN_CONFIG.timeout}`);                 // 30000 — valor original preservado
console.groupEnd();

// ----- Object.seal() -----
console.group("Object.seal()");
const sealedProduct = Object.seal({
    id: 1,
    name: "Cadeira Gamer",
    price: 1299.90
});

sealedProduct.price = 999.90;     // Permitido — altera valor existente
// sealedProduct.stock = 50;      // ERRO — não pode adicionar
// delete sealedProduct.name;     // ERRO — não pode remover

console.log(`Está selado? ${Object.isSealed(sealedProduct)}`);        // true
console.log(`Aceita extensões? ${Object.isExtensible(sealedProduct)}`);// false
console.log(`price: ${sealedProduct.price}`);                          // 999.90
console.groupEnd();

// ----- Object.preventExtensions() -----
console.group("Object.preventExtensions()");
const restricted = Object.preventExtensions({
    id: 1,
    label: "Categoria"
});

restricted.label = "Nova Categoria"; // Permitido — altera existente
delete restricted.id;                 // Permitido — remove existente
// restricted.nova = "teste";         // ERRO — não pode adicionar

console.log(`Aceita extensões? ${Object.isExtensible(restricted)}`);  // false
console.log("restricted:", restricted); // { label: "Nova Categoria" }
console.groupEnd();

// ----- QUADRO RESUMO -----
console.group("Quadro Resumo");
console.table({
    "Normal":             { Adicionar: "✓", Remover: "✓", Modificar: "✓" },
    "preventExtensions":  { Adicionar: "✗", Remover: "✓", Modificar: "✓" },
    "seal":               { Adicionar: "✗", Remover: "✗", Modificar: "✓" },
    "freeze":             { Adicionar: "✗", Remover: "✗", Modificar: "✗" }
});
console.groupEnd();

// =====================================================
// 🏋️ EXERCÍCIOS — Semana 4
// =====================================================

// 1. Crie `const user = {name: "Ana"}`. Modifique propriedades,
//    adicione novas, delete existentes. Explique por que `const`
//    não impede mutação do objeto.

// 2. Crie um objeto `config` e aplique freeze, seal, preventExtensions.
//    Monte uma tabela comparando o que cada um permite/bloqueia.

// 3. Implemente `deepFreeze(object)` que congela o objeto e TODOS os
//    seus objetos aninhados recursivamente. Teste com 3 níveis.

// 4. Crie objetos representando perfis de usuário:
//    `admin` (pode tudo), `editor` (seal — modifica, não adiciona),
//    `reader` (freeze — somente leitura).
