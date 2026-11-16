<?php
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Método inválido.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

$id_pergunta = (string)($input['id'] ?? '');
$pergunta = trim($input['pergunta'] ?? '');
$respostas = $input['respostas'] ?? [];
$correta = $input['correta'] ?? null;

if (empty($id_pergunta) || empty($pergunta)) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'ID e texto da pergunta são obrigatórios.']);
    exit;
}

if (count(array_filter(array_map('trim', $respostas))) < 2) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Informe pelo menos 2 alternativas.']);
    exit;
}

if ($correta === null || empty(trim($respostas[$correta] ?? ''))) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Selecione uma alternativa preenchida como correta.']);
    exit;
}

$arq_perguntas = '../perguntas.txt';
$arq_respostas = '../respostas.txt';

$linhas_p = file($arq_perguntas, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$novas_p = [];
foreach ($linhas_p as $l) {
    $d = explode(';', $l);
    if ($d[0] === $id_pergunta) {
        $pergunta_limpa = str_replace(';', '', $pergunta);
        $novas_p[] = "$id_pergunta;$pergunta_limpa";
    } else {
        $novas_p[] = $l;
    }
}
file_put_contents($arq_perguntas, implode(PHP_EOL, $novas_p) . PHP_EOL);

$linhas_r = file($arq_respostas, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$novas_r = [];
foreach ($linhas_r as $l) {
    $d = explode(';', $l);
    if (($d[1] ?? '') !== $id_pergunta) {
        $novas_r[] = $l;
    }
}

$ultimo_id_r = 0;
foreach ($novas_r as $l) {
    $d = explode(';', $l);
    if ((int)$d[0] > $ultimo_id_r) $ultimo_id_r = (int)$d[0];
}

foreach ($respostas as $index => $texto_r) {
    $texto_r = trim($texto_r);
    if (!empty($texto_r)) {
        $ultimo_id_r++;
        $is_correta = ((int)$correta === (int)$index) ? 1 : 0;
        $texto_r_limpo = str_replace(';', '', $texto_r);
        $novas_r[] = "$ultimo_id_r;$id_pergunta;$texto_r_limpo;$is_correta";
    }
}

file_put_contents($arq_respostas, implode(PHP_EOL, $novas_r) . PHP_EOL);

echo json_encode(['sucesso' => true, 'mensagem' => 'Pergunta atualizada com sucesso!']);