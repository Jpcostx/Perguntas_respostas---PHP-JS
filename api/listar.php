<?php
header('Content-Type: application/json; charset=utf-8');

$arq_perguntas = '../perguntas.txt';
$arq_respostas = '../respostas.txt';

if (!file_exists($arq_perguntas)) touch($arq_perguntas);
if (!file_exists($arq_respostas)) touch($arq_respostas);

$linhas_perguntas = file($arq_perguntas, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$linhas_respostas = file($arq_respostas, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

$contagem_respostas = [];
foreach ($linhas_respostas as $linha) {
    $dados = explode(';', $linha);
    $id_p = $dados[1] ?? '';
    if ($id_p) {
        $contagem_respostas[$id_p] = ($contagem_respostas[$id_p] ?? 0) + 1;
    }
}

$lista = [];
foreach ($linhas_perguntas as $linha) {
    $dados = explode(';', $linha);
    $id = $dados[0] ?? '';
    $texto = $dados[1] ?? '';
    $lista[] = [
        'id' => $id,
        'texto' => $texto,
        'total_opcoes' => $contagem_respostas[$id] ?? 0
    ];
}

echo json_encode(['sucesso' => true, 'dados' => $lista]);