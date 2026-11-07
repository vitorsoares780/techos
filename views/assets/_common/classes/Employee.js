import HttpClientBase from "./HttpClientBase";

export default class Employee extends HttpClientBase {
    #id;
    #user_id;
    #company_id;
    #active;

    constructor({ id = null, user_id = null, company_id = null, active = 0 } = {}) {
        this.id = id;
        this.user_id = user_id;
        this.company_id = company_id;
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

    get company_id() {
        return this.#company_id;
    }

    set company_id(value) {
        this.#company_id = value === null ? null : Number(value);
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
            user_id: this.#user_id,
            company_id: this.#company_id,
            active: this.#active
        };
    }

    static fromJSON(json) {
        return new Employee({
            id: json.id,
            user_id: json.user_id,
            company_id: json.company_id,
            active: json.active
        });
    }

    async listAll() {
        return this.get("/employees/list");
    }

    async listById(id) {
        return this.get(`/employees/list/${id}`);
    }

    async insert() {
        return this.post("/employees", this.toJSON());
    }

    async update() {
        return this.put(`/employees/${this.#id}`, this.toJSON());
    }

    async delete() {
        return this.delete(`/employees/${this.#id}`);
    }
}