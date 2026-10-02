import HttpClientBase from "./HttpClientBase";

export default class Product extends HttpClientBase {
    #id;
    #category_id;
    #name;
    #price;
    #active;
    #data_cadastro;

    constructor({ id = null, category_id = null, name = "", price = 0, active = 0, data_cadastro = null } = {}) {
        this.id = id;
        this.category_id = category_id;
        this.name = name;
        this.price = price;
        this.active = active;
        this.data_cadastro = data_cadastro;
    }

    get id() {
        return this.#id;
    }

    set id(value) {
        this.#id = value === null ? null : Number(value);
    }

    get category_id() {
        return this.#category_id;
    }

    set category_id(value) {
        this.#category_id = value === null ? null : Number(value);
    }

    get name() {
        return this.#name;
    }

    set name(value) {
        if (typeof value !== "string" || value.trim() === "") {
            throw new TypeError("O nome é obrigatório");
        }
        this.#name = value.trim();
    }

    get price() {
        return this.#price;
    }

    set price(value) {
        const num = Number(value);
        if (isNaN(num) || num < 0) {
            throw new TypeError("O preço deve ser um número válido maior ou igual a zero");
        }
        this.#price = num;
    }

    get active() {
        return this.#active;
    }

    set active(value) {
        const num = Number(value);
        if (isNaN(num) || (num !== 0 && num !== 1)) {
            throw new TypeError("O status deve ser 0 ou 1");
        }
        this.#active = num;
    }

    get data_cadastro() {
        return this.#data_cadastro;
    }

    set data_cadastro(value) {
        this.#data_cadastro = value;
    }

    toJSON() {
        return {
            id: this.#id,
            category_id: this.#category_id,
            name: this.#name,
            price: this.#price,
            active: this.#active,
            data_cadastro: this.#data_cadastro
        };
    }

    static fromJSON(json) {
        return new Product({
            id: json.id,
            category_id: json.category_id,
            name: json.name,
            price: json.price,
            active: json.active,
            data_cadastro: json.data_cadastro
        });
    }

    async listAll() {
        return this.get("/products/list");
    }

    async listById(id) {
        return this.get(`/products/list/${id}`);
    }

    async insert() {
        return this.post("/products", this.toJSON());
    }

    async update() {
        return this.put(`/products/${this.#id}`, this.toJSON());
    }

    async delete() {
        return this.delete(`/products/${this.#id}`);
    }
}