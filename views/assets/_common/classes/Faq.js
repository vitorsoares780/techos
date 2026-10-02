import HttpClientBase from "./HttpClientBase";

export default class Faq extends HttpClientBase {
    #id;
    #category_id;
    #question;
    #answer;
    #active;

    constructor({ id = null, category_id = null, question = "", answer = "", active = 0 } = {}) {
        this.id = id;
        this.category_id = category_id;
        this.question = question;
        this.answer = answer;
        this.active = active;
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

    get question() {
        return this.#question;
    }

    set question(value) {
        if (typeof value !== "string" || value.trim() === "") {
            throw new TypeError("A pergunta é obrigatória");
        }
        this.#question = value.trim();
    }

    get answer() {
        return this.#answer;
    }

    set answer(value) {
        if (typeof value !== "string" || value.trim() === "") {
            throw new TypeError("A resposta é obrigatória");
        }
        this.#answer = value.trim();
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
            category_id: this.#category_id,
            question: this.#question,
            answer: this.#answer,
            active: this.#active
        };
    }

    static fromJSON(json) {
        return new Faq({
            id: json.id,
            category_id: json.category_id,
            question: json.question,
            answer: json.answer,
            active: json.active
        });
    }

    async listAll() {
        return this.get("/faqs/list");
    }

    async listById(id) {
        return this.get(`/faqs/list/${id}`);
    }

    async insert() {
        return this.post("/faqs/insert", this.toJSON());
    }

    async update() {
        return this.put(`/faqs/update/${this.#id}`, this.toJSON());
    }

    async delete() {
        return this.delete(`/faqs/${this.#id}`);
    }
}