const ToastPrototype = {

    init() {
        if (this.container) {
            return this;
        }

        this.container = document.createElement("div");
        this.container.className = "toast-container";

        this.container.setAttribute("aria-live", "polite");
        this.container.setAttribute("aria-atomic", "true");

        document.body.appendChild(this.container);

        return this;
    },

    show(type, response, duration = 4000) {
        this.init();

        const message = this.extractMessage(response);
        const normalizedType = this.normalizeType(type);

        const toast = document.createElement("div");

        toast.className = `toast toast-${normalizedType}`;

        toast.setAttribute(
            "role",
            normalizedType === "error" ? "alert" : "status"
        );

        const messageElement = document.createElement("span");

        messageElement.className = "toast-message";
        messageElement.textContent = message;

        toast.appendChild(messageElement);
        this.container.appendChild(toast);

        window.setTimeout(() => {
            toast.remove();
        }, duration);

        return toast;
    },

    success(response, duration = 4000) {
        return this.show("success", response, duration);
    },

    warning(response, duration = 4000) {
        return this.show("warning", response, duration);
    },

    error(response, duration = 4000) {
        return this.show("error", response, duration);
    },

    normalizeType(type) {
        const validTypes = [
            "success",
            "warning",
            "error"
        ];

        if (!validTypes.includes(type)) {
            throw new Error(`Tipo de Toast inesperado: ${type}`);
        }

        return type;
    },

    extractMessage(response) {

        // Resposta já sendo uma string
        if (typeof response === "string") {
            return response;
        }

        // Resposta inválida
        if (
            response === null ||
            typeof response !== "object" ||
            Array.isArray(response)
        ) {
            throw new Error("Resposta inesperada para o Toast.");
        }

        // Exemplo:
        // { message: "Usuário cadastrado!" }
        if (
            typeof response.message === "string" &&
            response.message.trim() !== ""
        ) {
            return response.message.trim();
        }

        // Exemplo:
        // {
        //     data: {
        //         message: "Usuário cadastrado!"
        //     }
        // }
        if (
            response.data &&
            typeof response.data === "object" &&
            !Array.isArray(response.data) &&
            typeof response.data.message === "string" &&
            response.data.message.trim() !== ""
        ) {
            return response.data.message.trim();
        }

        throw new Error(
            "Resposta da API não contém uma propriedade 'message' válida."
        );
    }
};


// Objetos criados a partir do mesmo protótipo

const toastPrincipal = Object.create(ToastPrototype);

const toastSecundario = Object.create(ToastPrototype);


export {
    ToastPrototype,
    toastPrincipal,
    toastSecundario
};