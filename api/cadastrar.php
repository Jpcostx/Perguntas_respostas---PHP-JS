<?php
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Método inválido.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

$pergunta = trim($input['pergunta'] ?? '');
$respostas = $input['respostas'] ?? [];
$correta = $input['correta'] ?? null;

if (empty($pergunta)) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'O texto da pergunta é obrigatório.']);
    exit;
}

if (count(array_filter(array_map('trim', $respostas))) < 2) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Cadastre pelo menos 2 alternativas de resposta.']);
    exit;
}

if ($correta === null || empty(trim($respostas[$correta] ?? ''))) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Selecione uma alternativa preenchida como correta.']);
    exit;
}

$arq_perguntas = '../perguntas.txt';
$arq_respostas = '../respostas.txt';

$linhas_p = file_exists($arq_perguntas) ? file($arq_perguntas, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : [];
$ultimo_id_p = 0;
foreach ($linhas_p as $l) {
    $d = explode(';', $l);
    if ((int)$d[0] > $ultimo_id_p) $ultimo_id_p = (int)$d[0];
}
$novo_id_p = $ultimo_id_p + 1;

$pergunta_limpa = str_replace(';', '', $pergunta);
file_put_contents($arq_perguntas, "$novo_id_p;$pergunta_limpa" . PHP_EOL, FILE_APPEND);

$linhas_r = file_exists($arq_respostas) ? file($arq_respostas, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : [];
$ultimo_id_r = 0;
foreach ($linhas_r as $l) {
    $d = explode(';', $l);
    if ((int)$d[0] > $ultimo_id_r) $ultimo_id_r = (int)$d[0];
}

foreach ($respostas as $index => $texto_r) {
    $texto_r = trim($texto_r);
    if (!empty($texto_r)) {
        $ultimo_id_r++;
        $is_correta = ((int)$correta === (int)$index) ? 1 : 0;
        $texto_r_limpo = str_replace(';', '', $texto_r);
        file_put_contents($arq_respostas, "$ultimo_id_r;$novo_id_p;$texto_r_limpo;$is_correta" . PHP_EOL, FILE_APPEND);
    }
}

echo json_encode(['sucesso' => true, 'mensagem' => 'Pergunta cadastrada com sucesso!']);