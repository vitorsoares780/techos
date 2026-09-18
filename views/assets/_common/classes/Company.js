import HttpClientBase from "./HttpClientBase";

export default class Device extends HttpClientBase {
    #id;
    #cnpj;
    #name;
    #email;
    #owner_id;
    #plan_id;
    #creation_time;
    #active;

    constructor({
        id = null,
        cnpj = "",
        name = "",
        email = "",
        owner_id = null,
        plan_id = null,
        creation_time = null,
        active = 0
    } = {}) {
        this.id = id;
        this.cnpj = cnpj;
        this.name = name;
        this.email = email;
        this.owner_id = owner_id;
        this.plan_id = plan_id;
        this.creation_time = creation_time;
        this.active = active;
    }

    get id() {
        return this.#id;
    }

    set id(value) {
        this.#id = value === null ? null : Number(value);
    }

    get cnpj() {
        return this.#cnpj;
    }

    set cnpj(value) {
        if (typeof value !== "string" || value.trim() === "") {
            throw new TypeError("O CNPJ é obrigatório");
        }
        this.#cnpj = value.trim();
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
    get email() {
        return this.#email;
    }

    set email(value) {
        if (typeof value !== "string" || value.trim() === "") {
            throw new TypeError("O email é obrigatório");
        }
        this.#email = value.trim();
    }
    get owner_id() {
        return this.#owner_id;
    }

    set owner_id(value) {
        this.#owner_id = value === null ? null : Number(value);
    }
    get plan_id() {
        return this.#plan_id;
    }

    set plan_id(value) {
        this.#plan_id = value === null ? null : Number(value);
    }

    get creation_time() {
        return this.#creation_time;
    }

    set creation_time(value) {
        const date = new Date(value);
        if (!(date instanceof Date) || isNaN(date.getTime())) {
            throw new RangeError("Data inválida");
        }
        this.#creation_time = number;
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
            cnpj: this.cnpj,
            name: this.name,
            email: this.email,
            owner_id: this.owner_id,
            plan_id: this.plan_id,
            creation_time: this.creation_time,
            active: this.active
        };
    }

    // ========= CRUD ===========

    async listAll() {
        return this.get("/companies/list");
    }

    async listById(id) {
        return this.get("/companies/list/:companyId", { companyId: id });
    }

    // async listPaginator(page = 1, perPage = 10) {
    //     return this.get("/companies/list/paginator/:page/:per_page", {
    //         page,
    //         per_page: perPage
    //     });
    // }

    async insert(data) {
        return this.post("/companies/", data);
    }

    async update(id, data) {
        return this.put("/companies/:companyId", data, {
            companyId: id
        });
    }

    async remove(id) {
        return this.delete("/companies/:companyId", {
            companyId: id
        });
    }
}