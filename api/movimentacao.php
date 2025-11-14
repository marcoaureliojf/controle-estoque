<?php
require_once("../funcoes.php");
header('Content-Type: application/json');

function response($data, $status = 200) {
    http_response_code($status);
    echo json_encode($data);
    exit;
}

// O parâmetro deve ser capturado como 'produto_id'
$produto_id = isset($_GET['produto_id']) ? intval($_GET['produto_id']) : null;

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        // A chave de busca deve ser $produto_id
        if (!$produto_id || $produto_id <= 0) {
            response(['error' => 'ID do produto (produto_id) inválido ou não informado'], 400);
        }
        
        // 1. Verificação se o produto existe na tabela 'produtos'
        $produto_existe = slq_simples("SELECT 1 FROM produtos WHERE id = $produto_id");
        if (!$produto_existe) {
             response(['error' => 'Produto não encontrado na base de dados.'], 404);
        }

        // 2. Detalhamento de movimentação de um produto
        // Uso de $produto_id na query
        $sql = "SELECT id, produto_id, tipo, quantidade, data 
                FROM movimentacao 
                WHERE produto_id = $produto_id 
                ORDER BY data DESC";
                
        $movs = slq_assoc($sql);
        
        // Se a query retornou um array vazio, o produto existe mas não tem movimentação.
        if (empty($movs)) {
            response(['message' => 'Produto encontrado, mas sem movimentações registradas.'], 200);
        } else {
            response($movs);
        }
        
        break;

    case 'POST':
        // O endpoint 'estoque.php' é responsável por registrar a movimentação
        // (UPDATE na tabela 'estoque'). No entanto, o registro detalhado
        // de cada movimento deve ser feito na tabela 'movimentacao'.
        // O código a seguir implementa a inserção de uma nova movimentação.

        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['produto_id']) || !isset($data['quantidade']) || !isset($data['tipo'])) {
            response(['error' => 'Campos obrigatórios: produto_id, quantidade, tipo'], 400);
        }

        $prod_id = intval($data['produto_id']);
        $quantidade = intval($data['quantidade']);
        $tipo = $data['tipo']; // 'entrada' ou 'saida'
        
        if ($quantidade <= 0) {
            response(['error' => 'Quantidade deve ser maior que zero.'], 400);
        }

        // Validação básica do tipo
        if (!in_array($tipo, ['entrada', 'saida'])) {
             response(['error' => 'Tipo inválido. Use "entrada" ou "saida".'], 400);
        }
        
        // A coluna 'data' será preenchida automaticamente com o NOW() do MySQL na maioria dos casos.
        // Se o seu banco não fizer isso, você pode adicionar a função NOW() na query.
        
        $sql = "INSERT INTO movimentacao (produto_id, tipo, quantidade, data) 
                VALUES ($prod_id, " . a($tipo) . ", $quantidade, NOW())";
                
        // sql_insert() retorna o ID ou false em caso de erro.
        $insert_id = sql_insert($sql, false); 
        
        if ($insert_id !== false) {
             response(['success' => 'Registro de movimentação criado com sucesso', 'id' => $insert_id], 201);
        } else {
             response(['error' => 'Erro ao registrar movimentação. Verifique se o produto_id é válido.'], 500);
        }
        
        break;

    default:
        response(['error' => 'Método não suportado'], 405);
}

?>