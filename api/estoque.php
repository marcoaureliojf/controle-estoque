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

$id = isset($_GET['id']) ? intval($_GET['id']) : null;

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        // CONSULTA AGORA É FEITA NA VIEW 'estoque_atual'
        if ($id) {
            // COALESCE garante 0 se for null
            $sql = "SELECT id, descricao, codbarras, COALESCE(quantidade, 0) as quantidade, minimo 
                    FROM estoque_atual 
                    WHERE id = $id";
            $estoque = slq_assoc($sql);
            if ($estoque) {
                response($estoque[0]);
            } else {
                response(['error' => 'Produto não encontrado'], 404);
            }
        } else {
            $sql = "SELECT id, descricao, codbarras, COALESCE(quantidade, 0) as quantidade, minimo 
                    FROM estoque_atual";
            $estoques = slq_assoc($sql);
            response($estoques);
        }
        break;

    case 'POST':
        // MOVIMENTAÇÃO DE ESTOQUE (Entrada ou Saída)
        $data = getRequestData();
        
        // Validação dos campos (Note que usamos 'produto' e não 'produto_id' para alinhar com seu pensamento, mas no banco é 'produto')
        if (!isset($data['produto_id']) || !isset($data['quantidade']) || !isset($data['tipo'])) {
            response(['error' => 'Campos obrigatórios: produto_id, quantidade, tipo'], 400);
        }

        $produto_id = intval($data['produto_id']);
        $qtd_input = intval($data['quantidade']);
        $tipo = $data['tipo']; 
        $descricao_mov = isset($data['descricao']) ? a($data['descricao']) : "NULL";

        if ($qtd_input <= 0) {
            response(['error' => 'A quantidade deve ser maior que zero.'], 400);
        }

        // Lógica da nova estrutura:
        // Entrada = Positivo / Saída = Negativo
        if ($tipo === 'entrada') {
            $qtd_final = $qtd_input;
        } elseif ($tipo === 'saida') {
            // Verifica saldo atual antes de permitir saída
            $atual = slq_assoc("SELECT quantidade FROM estoque_atual WHERE id = $produto_id", true);
            $saldo = $atual ? intval($atual['quantidade']) : 0;
            
            if ($qtd_input > $saldo) {
                response(['error' => "Estoque insuficiente. Atual: $saldo"], 409);
            }
            $qtd_final = $qtd_input * -1; // Torna negativo
        } else {
            response(['error' => 'Tipo inválido. Use entrada ou saida.'], 400);
        }

        // INSERE NA TABELA movimentacao
        // Colunas do seu script: quando, produto, quantidade, descricao
        $sql = "INSERT INTO movimentacao (quando, produto, quantidade, descricao) 
                VALUES (NOW(), $produto_id, $qtd_final, $descricao_mov)";

        $insert_id = sql_insert($sql, false);

        if ($insert_id) {
            response(['success' => 'Movimentação registrada com sucesso', 'id' => $insert_id]);
        } else {
            response(['error' => 'Erro ao registrar movimentação'], 500);
        }
        break;

    default:
        response(['error' => 'Método não suportado'], 405);
}
?>