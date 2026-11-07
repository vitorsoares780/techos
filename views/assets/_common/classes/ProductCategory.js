import HttpClientBase from "./HttpClientBase";

export default class ProductCategory extends HttpClientBase {
    #id;
    #name;

    constructor({ id = null, name = "" } = {}) {
        this.id = id;
        this.name = name;
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

    toJSON() {
        return {
            id: this.#id,
            name: this.#name
        };
    }

    static fromJSON(json) {
        return new ProductCategory({
            id: json.id,
            name: json.name
        });
    }

    async listAll() {
        return this.get("/products-categories/list");
    }

    async listById(id) {
        return this.get(`/products-categories/list/${id}`);
    }

    async insert() {
        return this.post("/products-categories", this.toJSON());
    }

    async update() {
        return this.put(`/products-categories/update/${this.#id}`, this.toJSON());
    }

    async delete() {
        return this.delete(`/products-categories/${this.#id}`);
    }
}