<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<style>
    .wiki-toc a { color: #adb5bd; text-decoration: none; display: block; padding: 4px 0; font-size: .875rem; }
    .wiki-toc a:hover, .wiki-toc a.active { color: #fff; }
    .wiki-toc .toc-section { color: #6c757d; font-size: .7rem; text-transform: uppercase; letter-spacing: 1px; margin: 12px 0 4px; }
    .wiki-section { scroll-margin-top: 80px; }
    .wiki-step { background: #f8f9fa; border-left: 3px solid #0d6efd; padding: .75rem 1rem; border-radius: 0 .375rem .375rem 0; margin-bottom: .5rem; }
    .wiki-tip  { background: #fff3cd; border-left: 3px solid #ffc107; padding: .75rem 1rem; border-radius: 0 .375rem .375rem 0; }
    .wiki-warn { background: #f8d7da; border-left: 3px solid #dc3545; padding: .75rem 1rem; border-radius: 0 .375rem .375rem 0; }
    .wiki-badge { font-size: .7rem; padding: .15rem .5rem; border-radius: 999px; font-weight: 600; }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="bi bi-book me-2"></i>Manual do Painel</h4>
</div>

<div class="row g-4">

    <!-- Sumário fixo -->
    <div class="col-lg-3 d-none d-lg-block">
        <div class="wiki-toc sticky-top pt-2" style="top:20px">
            <div class="toc-section">Geral</div>
            <a href="#dashboard">Dashboard</a>
            <a href="#clientes">Clientes</a>
            <a href="#projetos">Projetos</a>
            <div class="toc-section">Vendas</div>
            <a href="#captacao">Captação Maps</a>
            <a href="#prospectos">Prospectos</a>
            <a href="#fila">Fila de abordagem</a>
            <div class="toc-section">Comercial</div>
            <a href="#modelos">Modelos de contrato</a>
            <a href="#contratos">Contratos</a>
            <div class="toc-section">Suporte</div>
            <a href="#chamados">Chamados</a>
            <div class="toc-section">Área do cliente</div>
            <a href="#cliente-painel">O que o cliente vê</a>
        </div>
    </div>

    <!-- Conteúdo -->
    <div class="col-lg-9">

        <!-- ── Dashboard ──────────────────────────────────────────── -->
        <section class="wiki-section mb-5" id="dashboard">
            <h5 class="border-bottom pb-2 mb-3"><i class="bi bi-speedometer2 me-2 text-primary"></i>Dashboard</h5>
            <p>A primeira tela após o login. Mostra um resumo rápido do estado do negócio:</p>
            <div class="row g-3 mb-3">
                <div class="col-sm-6">
                    <div class="card border-primary">
                        <div class="card-body py-2">
                            <strong class="text-primary">Clientes</strong><br>
                            <small class="text-muted">Total de clientes cadastrados. Clique para ver a lista.</small>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="card border-success">
                        <div class="card-body py-2">
                            <strong class="text-success">Projetos</strong><br>
                            <small class="text-muted">Total de projetos criados. Clique para gerenciar.</small>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="card border-warning">
                        <div class="card-body py-2">
                            <strong class="text-warning">Chamados abertos</strong><br>
                            <small class="text-muted">Chamados aguardando resposta. Clique para atender.</small>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="card border-danger">
                        <div class="card-body py-2">
                            <strong class="text-danger">Prospectos novos</strong><br>
                            <small class="text-muted">Leads captados ainda não abordados. Clique para ver a fila.</small>
                        </div>
                    </div>
                </div>
            </div>
            <p>Abaixo das métricas aparece a tabela de <strong>clientes recentes</strong> — os 5 últimos cadastrados.</p>
        </section>

        <!-- ── Clientes ──────────────────────────────────────────── -->
        <section class="wiki-section mb-5" id="clientes">
            <h5 class="border-bottom pb-2 mb-3"><i class="bi bi-people me-2 text-primary"></i>Clientes</h5>
            <p>Gerencia os clientes que têm acesso à área do cliente em <code>localhost:8081</code>.</p>

            <h6 class="mt-3">Cadastrar um cliente manualmente</h6>
            <div class="wiki-step">1. Acesse <strong>Clientes</strong> no menu lateral</div>
            <div class="wiki-step">2. Clique em <strong>Novo cliente</strong></div>
            <div class="wiki-step">3. Preencha nome, e-mail e telefone → <strong>Salvar</strong></div>
            <div class="wiki-step">4. Uma senha temporária aparece em destaque — anote e envie para o cliente</div>

            <h6 class="mt-3">Entrar como cliente <span class="wiki-badge bg-secondary text-white">Impersonação</span></h6>
            <p>Permite ver exatamente o que o cliente vê no painel deles.</p>
            <div class="wiki-step">1. Abra a ficha do cliente</div>
            <div class="wiki-step">2. Clique em <strong>Entrar como cliente</strong></div>
            <div class="wiki-step">3. Você será redirecionado para o painel do cliente automaticamente</div>
            <div class="wiki-step">4. Para voltar ao admin: clique em <strong>Sair da impersonação</strong> no topo</div>

            <h6 class="mt-3">Resetar senha</h6>
            <p>Na ficha do cliente, clique em <strong>Resetar senha</strong> — uma nova senha temporária é gerada e exibida no painel.</p>
        </section>

        <!-- ── Projetos ──────────────────────────────────────────── -->
        <section class="wiki-section mb-5" id="projetos">
            <h5 class="border-bottom pb-2 mb-3"><i class="bi bi-folder me-2 text-primary"></i>Projetos</h5>
            <p>Cada projeto é vinculado a um cliente e representa um serviço contratado (ex: desenvolvimento de site).</p>

            <h6 class="mt-3">Criar um projeto</h6>
            <div class="wiki-step">1. Acesse <strong>Projetos → Novo projeto</strong></div>
            <div class="wiki-step">2. Selecione o cliente, dê um nome e defina o status inicial</div>
            <div class="wiki-step">3. Salve — o projeto aparece na área do cliente imediatamente</div>

            <h6 class="mt-3">Tarefas / etapas do projeto</h6>
            <p>Dentro de cada projeto você pode adicionar tarefas (etapas de entrega). O cliente pode <strong>aprovar</strong> ou <strong>solicitar revisão</strong> de cada etapa pelo painel deles.</p>

            <div class="wiki-tip mt-2">
                <strong>Dica:</strong> use tarefas para documentar o que foi entregue e criar um histórico que o cliente pode acompanhar.
            </div>
        </section>

        <!-- ── Captação Maps ──────────────────────────────────────── -->
        <section class="wiki-section mb-5" id="captacao">
            <h5 class="border-bottom pb-2 mb-3"><i class="bi bi-geo-alt me-2 text-primary"></i>Captação Google Maps</h5>
            <p>Busca automaticamente estabelecimentos no Google Maps que <strong>não têm site</strong> e os adiciona à lista de prospectos.</p>

            <h6 class="mt-3">Configurar pela primeira vez</h6>
            <div class="wiki-step">1. Acesse <a href="/admin/google-maps-import">Captação Maps</a> no menu</div>
            <div class="wiki-step">2. Cole sua <strong>API Key do Google</strong> (Google Cloud Console → Places API → Credenciais)</div>
            <div class="wiki-step">3. Ajuste <strong>avaliação mínima</strong> (padrão: 4.0) e <strong>reviews mínimos</strong> (padrão: 50)</div>
            <div class="wiki-step">4. Revise as buscas — cada linha é uma pesquisa no Maps (ex: <em>"restaurante Pinheiros São Paulo"</em>)</div>
            <div class="wiki-step">5. Clique em <strong>Salvar configurações</strong></div>

            <h6 class="mt-3">Iniciar captação</h6>
            <div class="wiki-step">1. Clique em <strong>Iniciar captação</strong></div>
            <div class="wiki-step">2. O log à direita mostra cada lead encontrado em tempo real</div>
            <div class="wiki-step">3. Ao terminar, aparece o resumo e o botão <strong>Ir para a fila</strong></div>

            <h6 class="mt-3">Filtros aplicados automaticamente</h6>
            <ul class="small">
                <li>✗ Tem site → ignorado (já tem presença digital)</li>
                <li>✗ Avaliação baixa ou poucas reviews → ignorado (lead fraco)</li>
                <li>✗ Sem telefone cadastrado → ignorado</li>
                <li>✗ Já existe no painel → ignorado (deduplicação)</li>
            </ul>

            <div class="wiki-tip">
                <strong>Frequência recomendada:</strong> rodar uma vez por semana, segunda-feira de manhã. A fila fica abastecida para a semana toda.
            </div>
        </section>

        <!-- ── Prospectos ──────────────────────────────────────────── -->
        <section class="wiki-section mb-5" id="prospectos">
            <h5 class="border-bottom pb-2 mb-3"><i class="bi bi-person-plus me-2 text-primary"></i>Prospectos</h5>
            <p>Pipeline de vendas com 6 estágios:</p>
            <div class="d-flex flex-wrap gap-2 mb-3">
                <span class="badge text-bg-primary">Novo</span>
                <span class="text-muted">→</span>
                <span class="badge text-bg-info">Contatado</span>
                <span class="text-muted">→</span>
                <span class="badge text-bg-warning">Qualificado</span>
                <span class="text-muted">→</span>
                <span class="badge text-bg-secondary">Proposta enviada</span>
                <span class="text-muted">→</span>
                <span class="badge text-bg-success">Ganho</span>
                <span class="text-muted">ou</span>
                <span class="badge text-bg-danger">Perdido</span>
            </div>

            <h6>Mudar status</h6>
            <p>Na lista de prospectos, use o <strong>select de status</strong> diretamente na linha — salva automaticamente via AJAX, sem precisar abrir a ficha.</p>

            <h6 class="mt-3">Converter em cliente</h6>
            <div class="wiki-step">1. Abra a ficha do prospecto</div>
            <div class="wiki-step">2. O prospecto precisa ter um <strong>e-mail cadastrado</strong></div>
            <div class="wiki-step">3. Clique em <strong>Converter em cliente</strong></div>
            <div class="wiki-step">4. Um novo acesso é criado com senha temporária — exibida em destaque</div>

            <h6 class="mt-3">Importar CSV</h6>
            <p>Se você tiver uma planilha de leads, acesse <strong>Importar CSV</strong> na lista de prospectos. O sistema aceita o formato do Outscraper e formatos similares.</p>
        </section>

        <!-- ── Fila ──────────────────────────────────────────────── -->
        <section class="wiki-section mb-5" id="fila">
            <h5 class="border-bottom pb-2 mb-3"><i class="bi bi-play-circle me-2 text-primary"></i>Fila de abordagem</h5>
            <p>Interface focada para abordar os prospectos um por vez, do mais avaliado para o menos.</p>

            <h6 class="mt-3">Passo a passo</h6>
            <div class="wiki-step">1. Acesse <strong>Fila de abordagem</strong> no menu</div>
            <div class="wiki-step">2. O primeiro lead da fila aparece com nome, avaliação, telefone e mensagem personalizada</div>
            <div class="wiki-step">3. Clique em <strong>Abrir WhatsApp</strong> — a mensagem já vem preenchida, só dar enviar</div>
            <div class="wiki-step">4. Volte ao painel e registre o resultado:</div>

            <div class="row g-2 my-2">
                <div class="col-sm-4">
                    <div class="card border-success">
                        <div class="card-body py-2 text-center">
                            <strong class="text-success">Contatado</strong><br>
                            <small class="text-muted">Mensagem enviada. Avança no pipeline.</small>
                        </div>
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="card border-warning">
                        <div class="card-body py-2 text-center">
                            <strong class="text-warning">Pular</strong><br>
                            <small class="text-muted">Deixa para depois. Volta ao reiniciar a fila.</small>
                        </div>
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="card border-danger">
                        <div class="card-body py-2 text-center">
                            <strong class="text-danger">Desqualificar</strong><br>
                            <small class="text-muted">Descarta o lead (status → Perdido).</small>
                        </div>
                    </div>
                </div>
            </div>

            <h6 class="mt-3">Atalhos de teclado</h6>
            <div class="d-flex flex-wrap gap-2">
                <span class="badge bg-dark fs-6">W</span> <small class="me-3 align-self-center">Abrir WhatsApp</small>
                <span class="badge bg-dark fs-6">C</span> <small class="me-3 align-self-center">Marcar Contatado</small>
                <span class="badge bg-dark fs-6">P</span> <small class="me-3 align-self-center">Pular</small>
                <span class="badge bg-dark fs-6">D</span> <small class="me-3 align-self-center">Desqualificar</small>
            </div>

            <div class="wiki-tip mt-3">
                <strong>Ritmo ideal:</strong> 10 a 20 leads por dia, entre 9h e 11h (fora do horário de movimento dos restaurantes).
            </div>
        </section>

        <!-- ── Modelos ──────────────────────────────────────────── -->
        <section class="wiki-section mb-5" id="modelos">
            <h5 class="border-bottom pb-2 mb-3"><i class="bi bi-file-earmark-ruled me-2 text-primary"></i>Modelos de contrato</h5>
            <p>Templates reutilizáveis para gerar contratos rapidamente. Crie um modelo padrão e use-o para todos os clientes.</p>

            <div class="wiki-step">1. Acesse <strong>Modelos</strong> no menu → <strong>Novo modelo</strong></div>
            <div class="wiki-step">2. Dê um nome (ex: "Contrato de Criação de Site")</div>
            <div class="wiki-step">3. Use o editor de texto para redigir o contrato padrão</div>
            <div class="wiki-step">4. Salve — o modelo fica disponível para todos os contratos futuros</div>
        </section>

        <!-- ── Contratos ──────────────────────────────────────────── -->
        <section class="wiki-section mb-5" id="contratos">
            <h5 class="border-bottom pb-2 mb-3"><i class="bi bi-file-earmark-text me-2 text-primary"></i>Contratos</h5>
            <p>Gera contratos digitais para os clientes assinarem pelo próprio painel.</p>

            <h6 class="mt-3">Criar e enviar um contrato</h6>
            <div class="wiki-step">1. Acesse <strong>Contratos → Novo contrato</strong></div>
            <div class="wiki-step">2. Selecione o cliente e o projeto</div>
            <div class="wiki-step">3. Escolha um <strong>modelo</strong> para pré-preencher o conteúdo (ou escreva manualmente)</div>
            <div class="wiki-step">4. Preencha valor, datas de início e fim</div>
            <div class="wiki-step">5. Clique em <strong>Salvar</strong> — o contrato fica em rascunho</div>
            <div class="wiki-step">6. Quando estiver pronto, clique em <strong>Enviar ao cliente</strong></div>

            <h6 class="mt-3">Aceite digital</h6>
            <p>Após o envio, o contrato aparece na área do cliente em <strong>Documentos → Contratos</strong>. O cliente lê e clica em <strong>Aceitar contrato</strong>. O sistema registra automaticamente:</p>
            <ul class="small">
                <li>Data e hora do aceite</li>
                <li>IP do dispositivo usado</li>
            </ul>
            <p>Essas informações ficam visíveis na ficha do contrato no painel admin.</p>

            <h6 class="mt-3">Status do contrato</h6>
            <div class="d-flex flex-wrap gap-2">
                <span class="badge text-bg-secondary">Rascunho</span>
                <span class="text-muted small align-self-center">→</span>
                <span class="badge text-bg-warning">Enviado</span>
                <span class="text-muted small align-self-center">→</span>
                <span class="badge text-bg-success">Aceito</span>
                <span class="text-muted small align-self-center">→</span>
                <span class="badge text-bg-dark">Encerrado</span>
            </div>
        </section>

        <!-- ── Chamados ──────────────────────────────────────────── -->
        <section class="wiki-section mb-5" id="chamados">
            <h5 class="border-bottom pb-2 mb-3"><i class="bi bi-headset me-2 text-primary"></i>Chamados de suporte</h5>
            <p>Clientes podem abrir chamados pela área deles. O admin recebe e responde por aqui.</p>

            <div class="wiki-step">1. Acesse <strong>Chamados</strong> no menu</div>
            <div class="wiki-step">2. Clique no chamado para abrir a conversa</div>
            <div class="wiki-step">3. Leia a mensagem e responda no campo de texto abaixo</div>
            <div class="wiki-step">4. O status muda automaticamente para <strong>Em atendimento</strong> na primeira resposta</div>

            <div class="wiki-tip mt-2">
                <strong>Dica:</strong> o número de chamados abertos aparece no Dashboard — boa prática é zerar toda manhã antes de começar outras atividades.
            </div>
        </section>

        <!-- ── Área do cliente ──────────────────────────────────── -->
        <section class="wiki-section mb-5" id="cliente-painel">
            <h5 class="border-bottom pb-2 mb-3"><i class="bi bi-person-circle me-2 text-primary"></i>O que o cliente vê</h5>
            <p>O cliente acessa o painel em <code>localhost:8081</code> (em produção, seria o domínio do cliente). As seções disponíveis são:</p>

            <ul>
                <li><strong>Projetos</strong> — acompanha o andamento, aprova ou pede revisão em cada etapa</li>
                <li><strong>Documentos</strong>
                    <ul>
                        <li><em>Contratos</em> — vê os contratos enviados, aceita digitalmente</li>
                        <li><em>Arquivos</em> — arquivos entregues pelo admin (ex: artes, documentos)</li>
                    </ul>
                </li>
                <li><strong>Suporte</strong> — abre chamados e acompanha respostas</li>
                <li><strong>Financeiro</strong> — visualiza faturas e histórico de pagamentos</li>
            </ul>

            <div class="wiki-warn mt-2">
                <strong>Atenção:</strong> para testar o painel do cliente sem precisar sair do admin, use a função <strong>Entrar como cliente</strong> na ficha do cliente.
            </div>
        </section>

    </div>
</div>

<script>
// Destaca o item ativo no sumário conforme o scroll
const sections = document.querySelectorAll('.wiki-section');
const tocLinks  = document.querySelectorAll('.wiki-toc a');

const observer = new IntersectionObserver(entries => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            tocLinks.forEach(a => a.classList.remove('active'));
            const active = document.querySelector('.wiki-toc a[href="#' + entry.target.id + '"]');
            if (active) active.classList.add('active');
        }
    });
}, { rootMargin: '-20% 0px -70% 0px' });

sections.forEach(s => observer.observe(s));
</script>

<?= $this->endSection() ?>
