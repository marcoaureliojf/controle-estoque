# controle-estoque

Este projeto é o esboço de um sistema simplificado de controle de estoque que permite realizar as seguintes operações:

* Cadastramento de produtos;
* Entrada de estoque de um produto;
* Saída de estoque de um produto;
* Relatório de estoque atual;
* Relatório de produtos com estoque abaixo do mínimo especificado;
* Detalhamento de movimentação de um produto.

## Novos Endpoints RESTful

O sistema agora conta com uma API RESTful para integração com sistemas externos. Os endpoints disponíveis são:

### Produtos

- **GET /api/produtos.php**  
  Lista todos os produtos.
- **GET /api/produtos.php?id=ID**  
  Detalha um produto específico.
- **POST /api/produtos.php**  
  Cadastra um novo produto.
- **PUT /api/produtos.php?id=ID**  
  Atualiza um produto existente.
- **DELETE /api/produtos.php?id=ID**  
  Remove um produto.

### Estoque

- **GET /api/estoque.php**  
  Consulta o estoque de todos os produtos.
- **GET /api/estoque.php?id=ID**  
  Consulta o estoque de um produto específico.
- **POST /api/estoque.php**  
  Registra entrada ou saída de estoque.  
  Campos obrigatórios: `produto_id`, `quantidade`, `tipo` (`entrada` ou `saida`).

### Movimentação

- **GET /api/movimentacao.php?produto_id=ID**  
  Consulta o histórico de movimentações de um produto.

## Exemplos de uso com curl

```bash
# Listar todos os produtos
curl -X GET http://localhost/controle-estoque/api/produtos.php

# Detalhar produto
curl -X GET "http://localhost/controle-estoque/api/produtos.php?id=1"

# Cadastrar produto
curl -X POST http://localhost/controle-estoque/api/produtos.php \
     -H "Content-Type: application/json" \
     -d '{"descricao":"Produto Teste","codbarras":"123456789","minimo":10}'

# Atualizar produto
curl -X PUT "http://localhost/controle-estoque/api/produtos.php?id=1" \
     -H "Content-Type: application/json" \
     -d '{"descricao":"Produto Atualizado","minimo":5}'

# Remover produto
curl -X DELETE "http://localhost/controle-estoque/api/produtos.php?id=1"

# Consultar estoque
curl -X GET http://localhost/controle-estoque/api/estoque.php

# Registrar entrada de estoque
curl -X POST http://localhost/controle-estoque/api/estoque.php \
     -H "Content-Type: application/json" \
     -d '{"produto_id":1,"quantidade":20,"tipo":"entrada"}'

# Registrar saída de estoque
curl -X POST http://localhost/controle-estoque/api/estoque.php \
     -H "Content-Type: application/json" \
     -d '{"produto_id":1,"quantidade":5,"tipo":"saida"}'

# Consultar movimentação de produto
curl -X GET "http://localhost/controle-estoque/api/movimentacao.php?produto_id=1"
```

> **Atenção:** Altere o endereço conforme o seu ambiente.

## Autor

Este projeto foi criado por [Renato Monteiro Batista](https://renato.ovh) e é disponibilizado gratuitamente pela [RMB Informática](https://rmbinformatica.com).

## Licença de uso

Este projeto pode ser utilizado, reproduzido, modificado e comercializado sem a prévia autorização do autor. Use como desejar.

## Requisitos para instalação e execução

Para a execução deste projeto é necessário:

* Servidor web, preferencialmente apache;
* Suporte a linguagem PHP;
* Banco de dados mysql (ver o arquivo `modelo.banco.php` para configuração das credenciais de acesso ao banco.)

Este projeto não possui nenhuma dependência, foi desenvolvido sem a utilização de nenhum framework e todos os arquivos necessários à execução deste projeto estão inclusos neste repositório, o que torna o software funcional mesmo em ambientes offline sem acesso à internet.

## Instruções de instalação

* Baixe o repositório em uma pasta acessível no seu servidor web.
* Configure o arquivo `modelo.banco.php` conforme suas credenciais de acesso ao banco de dados e renomeie-o para `banco.php`.
* Importe a estrutura do banco de dados especificada no arquivo `bd/banco.sql`.

## Suporte e customização

O projeto é fornecido como apresentado, sem custos de licenciamento e de suporte. Desta forma não será fornecido nenhum suporte gratuito com relação à implementação e execução.

Caso deseje obter suporte do desenvolvedor, auxílio na instalação/implementação ou qualquer modificação/customização, solicite um orçamento para o serviço específico através da área de contato do site da [RMB Informáica](https://rmbinformatica.com)

## Execução em container

Este projeto é compatível com a utilização do container [renatomb/alpine-apache-php-git](https://hub.docker.com/r/renatomb/alpine-apache-php-git).

## Gostou? Contribua!

Gostou do projeto e desja fazer uma contribuição ao autor? Faça uma doação de qualquer valor para:
* Pix: `f4757119-9e3f-4c92-b874-4f8590e6d969`;
* Bitcoin (BTC):  `3BS6f7u3W1bzB8u4pRDBcJCJsKPmeGYnQF`;
* Monero (XMR): `457h16yahCt8VWCTXySGRn9WDhhQ3aL8k5AY9RGF12Bu2Qh39xyurVmgLmQVeX6cADVinsN6KizP6BniuWAf6fQCKQW4Z6z`;
* Lumens (XLM): `GA664ZYUYKP25YBPBQ2BMW3YHZMP53MFR3VL36JKWM67KFEA7D27GTGU`;
* Dogecoin (DOGE): `DJa1CnX9d16xWX6kXvhsRrbZnpbj2hnwK4`;
* Bitcoin Cash (BCH): `qr3r5vy833z57xwphudvp6cwu9z5nwmg8vf4f672cq`;