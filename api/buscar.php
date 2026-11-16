<?php
header('Content-Type: application/json; charset=utf-8');

$id_pergunta = $_GET['id'] ?? null;

if (!$id_pergunta) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'ID não informado.']);
    exit;
}

$arq_perguntas = '../perguntas.txt';
$arq_respostas = '../respostas.txt';

$linhas_p = file_exists($arq_perguntas) ? file($arq_perguntas, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : [];
$linhas_r = file_exists($arq_respostas) ? file($arq_respostas, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : [];

$pergunta_encontrada = null;
foreach ($linhas_p as $l) {
    $d = explode(';', $l);
    if (($d[0] ?? '') === $id_pergunta) {
        $pergunta_encontrada = ['id' => $d[0], 'texto' => $d[1]];
        break;
    }
}

if (!$pergunta_encontrada) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Pergunta não encontrada.']);
    exit;
}

$respostas_encontradas = [];
foreach ($linhas_r as $l) {
    $d = explode(';', $l);
    if (($d[1] ?? '') === $id_pergunta) {
        $respostas_encontradas[] = [
            'id' => $d[0],
            'texto' => $d[2],
            'correta' => ($d[3] ?? '0') === '1'
        ];
    }
}

echo json_encode([
    'sucesso' => true,
    'pergunta' => $pergunta_encontrada,
    'respostas' => $respostas_encontradas
]);