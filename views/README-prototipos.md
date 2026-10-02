# Protótipos iniciais — POO prototípica

Esta etapa cria somente os componentes-base pedidos para a Parte B.
A implementação completa das funcionalidades será feita posteriormente.

## Componentes criados

### `Toast.js`

Componente reutilizável para mensagens da API.

Tipos previstos:

- `success` → verde
- `warning` → amarelo
- `error` → vermelho

O componente usa `Object.create`/protótipo como modelo de POO e mantém os
métodos compartilhados em `Toast`.

O tratamento inicial:

- aceita uma mensagem diretamente como string;
- aceita uma resposta com `message`;
- aceita uma resposta com `data.message`;
- não transforma uma data em mensagem;
- lança erro quando recebe uma resposta inesperada, em vez de silenciar o problema.

> A remoção automática, animações e integração definitiva com `HttpClientBase`
> ficam para a próxima etapa.

### `FormController.js`

Protótipo reservado para operações reutilizáveis de formulário:

- serialização;
- validação;
- limpeza.

### `CrudController.js`

Protótipo reservado para operações reutilizáveis de CRUD:

- listagem;
- edição;
- confirmação de exclusão.

### `ApiResponseAdapter.js`

Protótipo reservado para transformar respostas da API em entidades do
frontend.

## Outras opções que podemos criar depois

1. **ModalController**
   - Abrir, fechar e controlar modais de forma reutilizável.
   - Útil para formulários de criação/edição e confirmações.

2. **TableController**
   - Paginação, filtros, ordenação e renderização de tabelas.
   - Útil principalmente no painel administrativo.

3. **LoadingController**
   - Centralizar estados de carregamento de botões, formulários e listagens.
   - Evita repetir lógica de `loading` em cada tela.

4. **FormController**
   - Serializar, validar e limpar formulários.
   - Reduz código repetido em login, cadastro e edição.

5. **CrudController**
   - Concentrar o fluxo comum de listar → editar → excluir.
   - Pode servir de base para empresas, funcionários, dispositivos e ordens.

6. **ApiResponseAdapter**
   - Converter o formato da API para o formato usado pela interface.
   - Evita espalhar adaptações de payload pelo código.

## Próxima etapa

Depois de revisar a estrutura, podemos implementar um componente por vez,
começando pelo `Toast`, e então demonstrar explicitamente dois objetos criados
a partir do mesmo protótipo, com seus métodos compartilhados pelo protótipo.
