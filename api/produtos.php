<?php
require_once("../funcoes.php");
header('Content-Type: application/json');

// Função auxiliar para obter o corpo da requisição
function getRequestData() {
    return json_decode(file_get_contents('php://input'), true);
}

// Função auxiliar para resposta padrão
function response($data, $status = 200) {
    http_response_code($status);
    echo json_encode($data);
    exit;
}

// Identifica o ID do produto na URL (ex: /api/produtos.php?id=1)
$id = isset($_GET['id']) ? intval($_GET['id']) : null;

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        if ($id) {
            // Detalhar produto
            $produto = slq_assoc("SELECT id, descricao, codbarras, minimo FROM produtos WHERE id = $id", true);
            if ($produto) {
                response($produto);
            } else {
                response(['error' => 'Produto não encontrado'], 404);
            }
        } else {
            // Listar todos os produtos
            $produtos = slq_assoc("SELECT id, descricao, codbarras, minimo FROM produtos");
            response($produtos);
        }
        break;

    case 'POST':
        $data = getRequestData();
        if (!isset($data['descricao']) || !isset($data['codbarras']) || !isset($data['minimo'])) {
            response(['error' => 'Campos obrigatórios: descricao, codbarras, minimo'], 400);
        }
        
        $descricao = a($data['descricao']);
        $codbarras = a($data['codbarras']);
        $minimo = intval($data['minimo']);

        // Melhoria: Validação para garantir que 'minimo' não seja negativo (pois é UNSIGNED)
        if ($minimo < 0) {
            response(['error' => 'O campo mínimo de estoque não pode ser negativo.'], 400);
        }

        $sql = "INSERT INTO produtos (descricao, codbarras, minimo) VALUES ($descricao, $codbarras, $minimo)";
        
        $insert_id = sql_insert($sql, false); 
        
        if ($insert_id !== false) {
            response(['success' => 'Produto cadastrado com sucesso', 'id' => $insert_id], 201); 
        } else {
            response(['error' => 'Erro ao cadastrar produto. Possível duplicidade ou falha de banco.'], 500);
        }
        break;

    case 'PUT':
        if (!$id) {
            response(['error' => 'ID do produto não informado'], 400);
        }
        $data = getRequestData();
        $campos = [];

        if (isset($data['descricao'])) $campos[] = "descricao=" . a($data['descricao']);
        if (isset($data['codbarras'])) $campos[] = "codbarras=" . a($data['codbarras']);
        
        if (isset($data['minimo'])) {
            $minimo = intval($data['minimo']);
            // Melhoria: Validação para 'minimo'
            if ($minimo < 0) {
                response(['error' => 'O campo mínimo de estoque não pode ser negativo.'], 400);
            }
            $campos[] = "minimo=" . $minimo;
        }

        if (empty($campos)) {
            response(['error' => 'Nenhum campo para atualizar'], 400);
        }
        
        $sql = "UPDATE produtos SET " . implode(', ', $campos) . " WHERE id=$id";
        
        if (play_sql($sql)) {
            response(['success' => 'Produto atualizado com sucesso']);
        } else {
            response(['error' => 'Erro ao atualizar produto'], 500);
        }
        break;

    case 'DELETE':
        if (!$id) {
            response(['error' => 'ID do produto não informado'], 400);
        }
        
        $sql = "DELETE FROM produtos WHERE id=$id";
        
        $linhas_excluidas = delete_sql($sql);
        
        if ($linhas_excluidas > 0) {
            response(['success' => 'Produto removido com sucesso', 'linhas_afetadas' => $linhas_excluidas]);
        } elseif ($linhas_excluidas === 0) {
            response(['error' => 'Nenhum produto encontrado com o ID fornecido para exclusão.'], 404);
        } else {
            response(['error' => 'Erro ao remover produto'], 500);
        }
        break;

    default:
        response(['error' => 'Método não suportado'], 405);
}

?>