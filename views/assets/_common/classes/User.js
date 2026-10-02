import HttpClientBase from "./HttpClientBase.js";

export default class User extends HttpClientBase {
    #id;
    #type_id;
    #name;
    #email;
    #password;
    #photo;
    #active;

    constructor({
        id = null,
        type_id = null,
        name = null,
        email = null,
        password = "",
        photo = "",
        active = 0
    } = {}) {
        super();

        this.id = id;
        this.type_id = type_id;

        if (name !== null) {
            this.name = name;
        }

        if (email !== null) {
            this.email = email;
        }

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
        if (value === "" || value === null || value === undefined) {
            this.#password = "";
            return;
        }

        if (typeof value !== "string") {
            throw new TypeError("A senha deve ser um texto");
        }

        this.#password = value.trim();
    }

    get photo() {
        return this.#photo;
    }

    set photo(value) {
        this.#photo = value || "";
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
            type_id: this.type_id,
            name: this.name,
            email: this.email,
            password: this.password,
            photo: this.photo,
            active: this.active
        };
    }

    setToken(token) {
        if (token) {
            this.setAuthToken(token);
        }

        return this;
    }

    async login(email, password) {
        return this.postForm("/users/login", {
            email,
            password
        });
    }

    async loginFromForm(form) {
        return this.postForm("/users/login", form);
    }

    async loginAdmin(email, password) {
        return this.postForm("/users/login-admin", {
            email,
            password
        });
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

    async listAll() {
        return this.get("/users/list");
    }

    async update(data) {
        return this.put("/users/update", data);
    }

    async updateAdmin(data) {
        return this.put("/users/update-admin", data);
    }

    async remove(id) {
        return this.delete("/users/:userId", {
            userId: id
        });
    }
}