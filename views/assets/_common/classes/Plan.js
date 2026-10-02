import HttpClientBase from "./HttpClientBase";

export default class Plan extends HttpClientBase {
    #id;
    #name;
    #price;
    #active;

    constructor({ id = null, name = "", price = 0, active = 0 } = {}) {
        this.id = id;
        this.name = name;
        this.price = price;
        this.active = active;
    }

    get id() {
        return this.#id;
    }

    set id(value) {
        this.#id = value === null ? null : Number(value);
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

    toJSON() {
        return {
            id: this.#id,
            name: this.#name,
            price: this.#price,
            active: this.#active
        };
    }

    static fromJSON(json) {
        return new Plan({
            id: json.id,
            name: json.name,
            price: json.price,
            active: json.active
        });
    }

    async listAll() {
        return this.get("/plans/list");
    }

    async listById(id) {
        return this.get(`/plans/list/${id}`);
    }

    async insert() {
        return this.post("/plans", this.toJSON());
    }

    async update() {
        return this.put(`/plans/${this.#id}`, this.toJSON());
    }

    async delete() {
        return this.delete(`/plans/${this.#id}`);
    }
}