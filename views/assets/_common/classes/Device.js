import HttpClientBase from "./HttpClientBase";

export default class Device extends HttpClientBase {
    #id;
    #user_id;
    #category_id;
    #serial_number;
    #name;
    #model;
    #brand;
    #creation_time;
    #active;

    constructor({
        id = null,
        user_id = null,
        category_id = null,
        serial_number = "",
        name = "",
        model = "",
        brand = "",
        creation_time = null,
        active = 0
    } = {}) {
        this.id = id;
        this.user_id = user_id;
        this.category_id = category_id;
        this.serial_number = serial_number;
        this.name = name;
        this.model = model;
        this.brand = brand;
        this.creation_time = creation_time;
        this.active = active;
    }

    get id() {
        return this.#id;
    }

    set id(value) {
        this.#id = value === null ? null : Number(value);
    }
    get user_id() {
        return this.#user_id;
    }

    set user_id(value) {
        this.#user_id = value === null ? null : Number(value);
    }
    get category_id() {
        return this.#category_id;
    }

    set category_id(value) {
        this.#category_id = value === null ? null : Number(value);
    }

    get serial_number() {
        return this.#serial_number;
    }

    set serial_number(value) {
        if (typeof value !== "string" || value.trim() === "") {
            throw new TypeError("O número de série é obrigatório");
        }
        this.#serial_number = value.trim();
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
    get model() {
        return this.#model;
    }

    set model(value) {
        if (typeof value !== "string" || value.trim() === "") {
            throw new TypeError("O modelo é obrigatório");
        }
        this.#model = value.trim();
    }
    get brand() {
        return this.#brand;
    }

    set brand(value) {
        if (typeof value !== "string" || value.trim() === "") {
            throw new TypeError("A marca é obrigatória");
        }
        this.#brand = value.trim();
    }

    get creation_time() {
        return this.#creation_time;
    }

    set creation_time(value) {
        const date = new Date(value);
        if (!(date instanceof Date) || isNaN(date.getTime())) {
            throw new RangeError("Data inválida");
        }
        this.#creation_time = date;
    }
    get active() {
        return this.#active;
    }

    set active(value) {
        const number = Number(value);
        if (number !== 1 && number !== 0) {
            throw new RangeError("Valor inválido");
        }
        this.#active = number;
    }

    toJSON() {
        return { 
            id: this.id,
            user_id: this.user_id,
            category_id: this.category_id,
            serial_number: this.serial_number, 
            name: this.name, 
            model: this.model, 
            brand: this.brand, 
            creation_time: this.creation_time,
            active: this.active 
        };
    }

    // ========= CRUD ===========

    async listAll() {
        return this.get("/devices/list");
    }

    async listById(id) {
        return this.get("/devices/list/:deviceId", { deviceId: id });
    }

    // async listPaginator(page = 1, perPage = 10) {
    //     return this.get("/devices/list/paginator/:page/:per_page", {
    //         page,
    //         per_page: perPage
    //     });
    // }

    async insert(data) {
        return this.post("/devices/", data);
    }

    async update(id, data) {
        return this.put("/devices/:deviceId", data, {
            deviceId: id
        });
    }

    async remove(id) {
        return this.delete("/devices/:deviceId", {
            deviceId: id
        });
    }
}