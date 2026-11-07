# 📁 data-base/

## Banco de Dados — Scripts SQL

---

### 🎯 Para que serve esta pasta?

A pasta `data-base/` armazena os **scripts SQL** necessários para criar e popular o banco de
dados do projeto. Isso garante que qualquer pessoa da equipe consiga **recriar o banco**
facilmente.

---

### 📂 Conteúdo desta pasta

```
data-base/
├── dump-db-techos.sql       ← Estrutura e dados iniciais do banco
├── er.mwb.bak               ← Backup do modelo do MySQL Workbench
└── products.json            ← Dados de produtos em JSON
```

O dump cria o banco `db-techos`, suas tabelas e os dados iniciais definidos
no próprio script.

---

### 🚀 Como usar?

Em uma instalação nova, execute o dump pelo phpMyAdmin ou pelo terminal:
```bash
mysql -u root -p < data-base/dump-db-techos.sql
```

As tabelas usam `CREATE TABLE IF NOT EXISTS`, mas os inserts de dados iniciais
podem ser repetidos se o dump for executado novamente. Evite reaplicá-lo em um
banco já populado sem revisar os dados.

### Galeria de imagens das ordens de serviço

A tabela `service_order_images` mantém uma linha por imagem e referencia
`service_orders.id` por `service_order_id`. Ela armazena `image_path`,
`alt_text`, `position` e `created_at`; a chave estrangeira usa `ON UPDATE CASCADE`
e `ON DELETE CASCADE`.

O arquivo binário fica em `uploads/service-orders/`; o banco guarda somente o
caminho relativo. O dump cria a tabela em instalações novas. Esta versão do
repositório não contém uma pasta de migrations; em um banco existente, aplique
a definição da tabela `service_order_images` que está no
`dump-db-techos.sql` antes de usar a galeria. A exclusão lógica de uma OS pela
API também remove os registros e arquivos correspondentes.

---

### 🔐 Boas práticas

- Mantenha o dump sincronizado com o schema usado pela aplicação.
- Faça backup antes de alterar um banco existente e confira quais estruturas
  já foram aplicadas.
- Nunca inclua credenciais reais no dump.

---

> O dump contém o schema e os dados iniciais atuais do TechOS.
