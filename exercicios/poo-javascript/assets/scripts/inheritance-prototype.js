// =====================================================
// 8.5 — Herança por Meio de Protótipo
// (Object.create, prototype chain, property shadowing)
// =====================================================
// Domínio: Product → DigitalProduct → OnlineCourse
//
// NOTA: Em projetos reais, prefira composição (mixins) a cadeias
// de herança muito profundas. Veja os exemplos de mixins no final.
// Object.hasOwn(obj, prop) (ES2022) é mais seguro que obj.hasOwnProperty().
// =====================================================

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
console.group("Uso da cadeia de herança");
const jsCourse = Object.create(OnlineCourse).init(
    101, "JavaScript Avançado", 199.90, 4500, "Prof. Rafael", 40
);

console.log(`getInfo(): ${jsCourse.getInfo()}`);            // herdado de Product
console.log(`getDownloadInfo(): ${jsCourse.getDownloadInfo()}`); // herdado de DigitalProduct
console.log(`instructor: ${jsCourse.instructor}`);           // próprio
jsCourse.enroll();                                           // próprio
console.groupEnd();

// ----- Verificando a cadeia (prototype chain) -----
console.group("Verificação da cadeia");
console.log(`jsCourse → OnlineCourse: ${Object.getPrototypeOf(jsCourse) === OnlineCourse}`);
console.log(`OnlineCourse → DigitalProduct: ${Object.getPrototypeOf(OnlineCourse) === DigitalProduct}`);
console.log(`DigitalProduct → Product: ${Object.getPrototypeOf(DigitalProduct) === Product}`);
console.log(`Product → Object.prototype: ${Object.getPrototypeOf(Product) === Object.prototype}`);
console.log(`Fim da cadeia: ${Object.getPrototypeOf(Object.prototype)}`); // null
console.groupEnd();

// ----- Property shadowing (sombreamento) -----
console.group("Property shadowing");
const item = Object.create(Product);
item.name = "Nome local"; // sombreia qualquer name que existisse no protótipo

// ES2022: Object.hasOwn() é mais seguro que hasOwnProperty()
console.log(`name é própria? (Object.hasOwn): ${Object.hasOwn(item, "name")}`);
console.log(`getInfo é própria? (Object.hasOwn): ${Object.hasOwn(item, "getInfo")}`);
console.groupEnd();

// =====================================================
// COMPOSIÇÃO / MIXINS — Alternativa moderna à herança
// =====================================================

console.group("Composição / Mixins (alternativa à herança profunda)");

const canLog = {
    log() {
        console.log(`[LOG] ${JSON.stringify(this)}`);
        return this;
    }
};

const canSerialize = {
    toJSON() {
        return { id: this.id, name: this.name, price: this.price };
    }
};

const canDiscount = {
    applyDiscount(percentage) {
        this.price = this.price * (1 - percentage / 100);
        return this;
    }
};

// Compondo um objeto com múltiplos comportamentos
const smartProduct = Object.assign(
    Object.create(Product),
    canLog,
    canSerialize,
    canDiscount
).init(200, "Fone Bluetooth", 349.90);

smartProduct.log().applyDiscount(10).log();
console.log(`JSON: ${JSON.stringify(smartProduct.toJSON())}`);
console.groupEnd();

// =====================================================
// 🏋️ EXERCÍCIOS — Semana 5
// =====================================================

// 1. Crie uma cadeia: Animal → Mammal → Dog → myPet.
//    Cada nível adiciona propriedades/métodos.
//    Use Object.getPrototypeOf() e isPrototypeOf() para navegar/validar.

// 2. Crie `employee` que herda de `person`. Adicione `introduce()`
//    no filho que COMPLEMENTA (não substitui) o do pai.
//    Use Object.getPrototypeOf(this).introduce().

// 3. Crie vários objetos herdando de `Vehicle`. Adicione `start()`
//    ao protótipo DEPOIS que os objetos foram criados.
//    Demonstre que todos passam a ter o método.

// 4. Implemente `isVehicleType(object)` que retorna `true` se o objeto
//    herda (direta ou indiretamente) de um protótipo `Vehicle`.
//    Use isPrototypeOf() ou Object.getPrototypeOf().

// 5. (Extra) Use mixins para compor um objeto `smartDevice` com
//    comportamentos `canConnect`, `canUpdate`, `canReset` sem usar herança.
