const FormControllerPrototype = {

    init(form) {
        if (!(form instanceof HTMLFormElement)) {
            throw new TypeError("O elemento informado não é um formulário.");
        }

        this.form = form;

        return this;
    },

    getData() {
        if (!this.form) {
            throw new Error("O FormController não foi inicializado.");
        }

        const formData = new FormData(this.form);
        const data = {};

        for (const [name, value] of formData.entries()) {
            data[name] = value;
        }

        return data;
    },

    getValue(fieldName) {
        if (!this.form) {
            throw new Error("O FormController não foi inicializado.");
        }

        const field = this.form.elements.namedItem(fieldName);

        if (!field) {
            return null;
        }

        return field.value;
    },

    validateRequired(fields) {
        if (!this.form) {
            throw new Error("O FormController não foi inicializado.");
        }

        for (const fieldName of fields) {
            const field = this.form.elements.namedItem(fieldName);

            if (!field) {
                throw new Error(
                    `Campo "${fieldName}" não encontrado no formulário.`
                );
            }

            if (String(field.value).trim() === "") {
                return {
                    valid: false,
                    field: fieldName
                };
            }
        }

        return {
            valid: true,
            field: null
        };
    },

    clear() {
        if (!this.form) {
            throw new Error("O FormController não foi inicializado.");
        }

        this.form.reset();

        return this;
    }
};


// Dois objetos criados a partir do mesmo protótipo

const loginFormController = Object.create(FormControllerPrototype);

const userFormController = Object.create(FormControllerPrototype);


export {
    FormControllerPrototype,
    loginFormController,
    userFormController
};