import HttpClientBase from "./HttpClientBase";

export default class DeviceCategory extends HttpClientBase {
    #id;
    #name;
    #active;

    constructor({ id = null, name = "", active = 0 } = {}) {
        this.id = id;
        this.name = name;
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
            active: this.#active
        };
    }

    static fromJSON(json) {
        return new DeviceCategory({
            id: json.id,
            name: json.name,
            active: json.active
        });
    }

    async listAll() {
        return this.get("/devices-categories/list");
    }

    async listById(id) {
        return this.get(`/devices-categories/list/${id}`);
    }

    async insert() {
        return this.post("/devices-categories", this.toJSON());
    }

    async update() {
        return this.put(`/devices-categories/${this.#id}`, this.toJSON());
    }

    async delete() {
        return this.delete(`/devices-categories/${this.#id}`);
    }
}