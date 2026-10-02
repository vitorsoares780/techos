import HttpClientBase from "./HttpClientBase.js";

export default class ServiceOrder extends HttpClientBase {
    #id;
    #user_id;
    #device_id;
    #company_id;
    #defect;
    #status;
    #price;
    #photo;
    #creation_time;
    #active;

    constructor({
        id = null,
        user_id = null,
        device_id = null,
        company_id = null,
        defect = null,
        status = "",
        price = 0,
        photo = "",
        creation_time = null,
        active = 0
    } = {}) {
        super();

        this.id = id;
        this.user_id = user_id;
        this.device_id = device_id;
        this.company_id = company_id;

        if (defect !== null) {
            this.defect = defect;
        }

        this.status = status;
        this.price = price;
        this.photo = photo;

        if (creation_time !== null) {
            this.creation_time = creation_time;
        }

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

    get device_id() {
        return this.#device_id;
    }

    set device_id(value) {
        this.#device_id = value === null ? null : Number(value);
    }

    get company_id() {
        return this.#company_id;
    }

    set company_id(value) {
        this.#company_id = value === null ? null : Number(value);
    }

    get defect() {
        return this.#defect;
    }

    set defect(value) {
        if (typeof value !== "string" || value.trim() === "") {
            throw new TypeError("O defeito é obrigatório");
        }

        this.#defect = value.trim();
    }

    get status() {
        return this.#status;
    }

    set status(value) {
        this.#status = String(value || "").trim();
    }

    get price() {
        return this.#price;
    }

    set price(value) {
        const number = Number(value);

        if (!Number.isFinite(number) || number < 0) {
            throw new RangeError("O preço deve ser um número não negativo");
        }

        this.#price = number;
    }

    get photo() {
        return this.#photo;
    }

    set photo(value) {
        this.#photo = value || "";
    }

    get creation_time() {
        return this.#creation_time;
    }

    set creation_time(value) {
        const date = new Date(value);

        if (Number.isNaN(date.getTime())) {
            throw new RangeError("Data inválida");
        }

        this.#creation_time = date;
    }

    get active() {
        return this.#active;
    }

    set active(value) {
        const number = Number(value);

        if (number !== 0 && number !== 1) {
            throw new RangeError("O valor de ativo deve ser 0 ou 1");
        }

        this.#active = number;
    }

    toJSON() {
        return {
            id: this.id,
            user_id: this.user_id,
            device_id: this.device_id,
            company_id: this.company_id,
            defect: this.defect,
            status: this.status,
            price: this.price,
            photo: this.photo,
            creation_time: this.creation_time,
            active: this.active
        };
    }

    async listAll() {
        return this.get("/serviceOrders/list");
    }

    async listById(id) {
        return this.get("/serviceOrders/list/:serviceOrderId", {
            serviceOrderId: id
        });
    }

    async insert(data) {
        return this.post("/serviceOrders/", data);
    }

    async update(id, data) {
        return this.put("/serviceOrders/:serviceOrderId", data, {
            serviceOrderId: id
        });
    }

    async remove(id) {
        return this.delete("/serviceOrders/:serviceOrderId", {
            serviceOrderId: id
        });
    }
}
