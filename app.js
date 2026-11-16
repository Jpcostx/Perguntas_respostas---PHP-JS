const telaLista = document.getElementById('tela-lista');
const telaForm = document.getElementById('tela-form');
const telaDetalhes = document.getElementById('tela-detalhes');
const alertaBox = document.getElementById('alerta');

document.addEventListener('DOMContentLoaded', () => {
    carregarPerguntas();
});

function mostrarAlerta(mensagem, tipo = 'sucesso') {
    alertaBox.textContent = mensagem;
    alertaBox.className = `alerta ${tipo}`;
    setTimeout(() => {
        alertaBox.classList.add('oculto');
    }, 3500);
}

function mostrarTela(tela) {
    telaLista.classList.add('oculto');
    telaForm.classList.add('oculto');
    telaDetalhes.classList.add('oculto');
    tela.classList.remove('oculto');
}

function voltarParaLista() {
    mostrarTela(telaLista);
    carregarPerguntas();
}

async function carregarPerguntas() {
    const tbody = document.getElementById('tabela-corpo');
    try {
        const resposta = await fetch('api/listar.php');
        const resultado = await resposta.json();

        tbody.innerHTML = '';

        if (!resultado.dados || resultado.dados.length === 0) {
            tbody.innerHTML = '<tr><td colspan="4">Nenhuma pergunta cadastrada até o momento.</td></tr>';
            return;
        }

        resultado.dados.forEach(item => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td><strong>#${item.id}</strong></td>
                <td>${escaparHTML(item.texto)}</td>
                <td>${item.total_opcoes} opções</td>
                <td class="acoes-tabela">
                    <button class="btn btn-sm" onclick="visualizarPergunta('${item.id}')">Ver</button>
                    <button class="btn btn-sm btn-editar" onclick="abrirFormulario('${item.id}')">Editar</button>
                    <button class="btn btn-sm btn-erro" onclick="excluirPergunta('${item.id}')">Excluir</button>
                </td>
            `;
            tbody.appendChild(tr);
        });
    } catch (erro) {
        mostrarAlerta('Erro ao carregar as perguntas do servidor.', 'erro');
    }
}

async function visualizarPergunta(id) {
    try {
        const resposta = await fetch(`api/buscar.php?id=${encodeURIComponent(id)}`);
        const resultado = await resposta.json();

        if (!resultado.sucesso) {
            mostrarAlerta(resultado.mensagem, 'erro');
            return;
        }

        document.getElementById('detalhe-titulo').textContent = `Pergunta #${resultado.pergunta.id}`;
        document.getElementById('detalhe-texto').textContent = resultado.pergunta.texto;

        const listaUl = document.getElementById('detalhe-respostas');
        listaUl.innerHTML = '';

        resultado.respostas.forEach(r => {
            const li = document.createElement('li');
            if (r.correta) li.classList.add('resposta-correta');
            li.innerHTML = `
                <span>${escaparHTML(r.texto)}</span>
                ${r.correta ? '<span class="tag-correta">✓ Correta</span>' : ''}
            `;
            listaUl.appendChild(li);
        });

        document.getElementById('btn-detalhe-editar').onclick = () => abrirFormulario(id);
        mostrarTela(telaDetalhes);
    } catch (erro) {
        mostrarAlerta('Erro ao buscar detalhes da pergunta.', 'erro');
    }
}

async function abrirFormulario(id = null) {
    const form = document.getElementById('form-pergunta');
    form.reset();
    document.getElementById('pergunta-id').value = '';

    const inputsRespostas = document.querySelectorAll('.input-resposta');
    const radios = document.querySelectorAll('input[name="correta"]');

    if (!id) {
        document.getElementById('titulo-form').textContent = 'Criar Nova Pergunta';
        radios[0].checked = true;
        mostrarTela(telaForm);
        return;
    }

    try {
        const resposta = await fetch(`api/buscar.php?id=${encodeURIComponent(id)}`);
        const resultado = await resposta.json();

        if (!resultado.sucesso) {
            mostrarAlerta(resultado.mensagem, 'erro');
            return;
        }

        document.getElementById('titulo-form').textContent = `Editar Pergunta #${id}`;
        document.getElementById('pergunta-id').value = id;
        document.getElementById('pergunta-texto').value = resultado.pergunta.texto;

        inputsRespostas.forEach((input, idx) => {
            const resp = resultado.respostas[idx];
            input.value = resp ? resp.texto : '';
            if (resp && resp.correta) {
                radios[idx].checked = true;
            }
        });

        mostrarTela(telaForm);
    } catch (erro) {
        mostrarAlerta('Erro ao carregar dados para edição.', 'erro');
    }
}

document.getElementById('form-pergunta').addEventListener('submit', async (e) => {
    e.preventDefault();

    const id = document.getElementById('pergunta-id').value;
    const pergunta = document.getElementById('pergunta-texto').value;
    const inputsRespostas = document.querySelectorAll('.input-resposta');
    const radioSelecionado = document.querySelector('input[name="correta"]:checked');

    const respostas = Array.from(inputsRespostas).map(input => input.value);
    const correta = radioSelecionado ? parseInt(radioSelecionado.value) : null;

    const url = id ? 'api/editar.php' : 'api/cadastrar.php';
    const payload = { id, pergunta, respostas, correta };

    try {
        const resposta = await fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });

        const resultado = await resposta.json();

        if (resultado.sucesso) {
            mostrarAlerta(resultado.mensagem, 'sucesso');
            voltarParaLista();
        } else {
            mostrarAlerta(resultado.mensagem, 'erro');
        }
    } catch (erro) {
        mostrarAlerta('Erro de comunicação com o servidor.', 'erro');
    }
});

async function excluirPergunta(id) {
    if (!confirm('Excluir esta pergunta e todas as suas respostas?')) return;

    try {
        const resposta = await fetch('api/excluir.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id })
        });

        const resultado = await resposta.json();

        if (resultado.sucesso) {
            mostrarAlerta(resultado.mensagem, 'sucesso');
            carregarPerguntas();
        } else {
            mostrarAlerta(resultado.mensagem, 'erro');
        }
    } catch (erro) {
        mostrarAlerta('Erro ao excluir a pergunta.', 'erro');
    }
}

function escaparHTML(texto) {
    const div = document.createElement('div');
    div.textContent = texto;
    return div.innerHTML;
}