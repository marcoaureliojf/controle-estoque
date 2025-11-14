<?php
require_once("../funcoes.php");
header('Content-Type: application/json');

function getRequestData() {
    return json_decode(file_get_contents('php://input'), true);
}

function response($data, $status = 200) {
    http_response_code($status);
    echo json_encode($data);
    exit;
}

// O parâmetro deve ser capturado como 'produto_id'
$produto_id = isset($_GET['produto_id']) ? intval($_GET['produto_id']) : null;

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        if (!$produto_id || $produto_id <= 0) {
            response(['error' => 'ID do produto (produto_id) inválido ou não informado'], 400);
        }
        
        // 1. Verificação se o produto existe na tabela 'produtos'
        $produto_existe = sql_simples("SELECT 1 FROM produtos WHERE id = $produto_id");
        if (!$produto_existe) {
             response(['error' => 'Produto não encontrado na base de dados.'], 404);
        }

        // 2. Detalhamento de movimentação de um produto
        // CORREÇÃO: Usar os novos nomes de colunas: 'produto' e 'quando'
        $sql = "SELECT 
                    id, 
                    produto AS produto_id, 
                    quantidade, 
                    descricao,
                    quando AS data,
                    -- Adiciona um campo de tipo simulado para compatibilidade com o frontend
                    CASE WHEN quantidade > 0 THEN 'entrada' ELSE 'saida' END AS tipo 
                FROM movimentacao 
                WHERE produto = $produto_id 
                ORDER BY quando DESC";
                
        $movs = slq_assoc($sql);
        
        if (empty($movs)) {
            response(['message' => 'Produto encontrado, mas sem movimentações registradas.'], 200);
        } else {
            response($movs);
        }
        
        break;

    case 'POST':
        // A tabela movimentacao registra ENTRADA como POSITIVO e SAÍDA como NEGATIVO.
        $data = getRequestData();

        if (!isset($data['produto_id']) || !isset($data['quantidade']) || !isset($data['tipo'])) {
            response(['error' => 'Campos obrigatórios: produto_id, quantidade, tipo'], 400);
        }

        $prod_id = intval($data['produto_id']);
        $qtd_input = intval($data['quantidade']);
        $tipo = strtolower($data['tipo']); // 'entrada' ou 'saida'
        $descricao = isset($data['descricao']) ? a($data['descricao']) : "NULL"; // Novo campo 'descricao'
        
        if ($qtd_input <= 0) {
            response(['error' => 'Quantidade deve ser maior que zero.'], 400);
        }

        // CORREÇÃO: Definir a quantidade como negativa para 'saida'
        if ($tipo === 'saida') {
            $quantidade_final = $qtd_input * -1;
        } elseif ($tipo === 'entrada') {
            $quantidade_final = $qtd_input;
        } else {
             response(['error' => 'Tipo inválido. Use "entrada" ou "saida".'], 400);
        }
        
        // CORREÇÃO: Usar os novos nomes de colunas: 'produto', 'quando' e 'descricao'
        // ATENÇÃO: A tabela movimentacao não tem mais a coluna 'tipo'
        $sql = "INSERT INTO movimentacao (produto, quantidade, descricao, quando) 
                VALUES ($prod_id, $quantidade_final, $descricao, NOW())";
                
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