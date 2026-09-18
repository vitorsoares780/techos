import HttpClientBase from "./HttpClientBase";

export default class User extends HttpClientBase {
    #id;
    #type_id;
    #name;
    #email;
    #password;
    #photo;
    #active;
    

    constructor({ id = null, type_id = null, name = "", email = "", password = "", photo = "", active = 0 } = {}) {
        this.id = id;
        this.type_id = type_id;
        this.name = name;
        this.email = email;
        this.password = password;
        this.photo = photo;
        this.active = active;
    }

    get id() {
        return this.#id;
    }

    set id(value) {
        this.#id = value === null ? null : Number(value);
    }
    get type_id() {
        return this.#type_id;
    }
    set type_id(value) {
        this.#type_id = value === null ? null : Number(value);
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
    get password() {
        return this.#password;
    }
    set password(value) {
        if (typeof value !== "string" || value.trim() === "") {
            throw new TypeError("A senha é obrigatória");
        }
        this.#password = value.trim();
    }
    get photo() {
        return this.#photo;
    }
    set photo(value) {
        this.#photo = value;
    }
    get active() {
        return this.#active;
    }
    set active(value) {
        const number = Number(value);
        if (number !== 0 && number !== 1) {
            throw new RangeError("O valor de ativo deve ser 0 ou 1");
        }
        this.#active = value === null ? null : Number(value);
    }

    toJSON() {
        return { id: this.id, type_id: this.type_id, name: this.name, email: this.email, password: this.password, photo: this.photo, active: this.active };
    }
     async login(email, password) {
        return this.postForm("/users/login", { email, password });
    }

    async loginFromForm(form) {
        return this.postForm("/users/login", form);
    }

    async loginAdmin(email, password) {
        return this.postForm("/users/login-admin", { email, password });
    }

    async loginAdminFromForm(form) {
        return this.postForm("/users/login-admin", form);
    }

    async register(data) {
        return this.postForm("/users/register", data);
    }
    async registerAdmin(data) {
        return this.postForm("/users/register-admin", data);
    }

    async update(data) {
        return this.put("/users/update", data);
    }

    async updateAdmin(data) {
        return this.put("/users/update-admin", data);
    }

}