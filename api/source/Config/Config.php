git <?php

const CONF_URL_BASE = "http://localhost/techos "; // URL base do site, geralmente localhost em desenvolvimento
const CONF_URL_TEST = "http://localhost/techos "; // URL base do site, geralmente localhost em desenvolvimento


const CONF_DB_HOST = "localhost";
const CONF_DB_NAME = "db-techos";
const CONF_DB_USER = "root";
const CONF_DB_PORT = "3306";
const CONF_DB_PASS = "";

// Chave secreta para criação do token JWT, deve ser uma string complexa e única para cada aplicação
// para gerar a sua acesse: https://jwtsecrets.com/
const JWT_SECRET_KEY = "10e45caf547b5b506b38e5beb451be9669c53f76e177b288bede625b24e43db19f32c1594533e4e4b6b025b5b54e09aa1a82626dbd608305fa9184a587ff6efa";