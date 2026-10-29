<?php
defined('ABSPATH') || exit;

/**
 * Páginas legais (LGPD): Política de Privacidade e Termos de Uso.
 *
 * Criadas UMA vez como RASCUNHO (draft). Fluxo:
 *   1. Preencha os trechos marcados [PREENCHER] (razão social, CNPJ, e-mail do encarregado...).
 *   2. Valide o texto com um advogado.
 *   3. Publique em Páginas > Todas as páginas. O rodapé e o formulário passam a linkar automaticamente.
 *
 * Para recriar após editar este arquivo: apague as duas páginas (inclusive da lixeira) e
 * remova a opção "gps_legal_pages_v1" (wp option delete gps_legal_pages_v1).
 */

const GPS_LEGAL_SLUG_PRIVACY = 'politica-de-privacidade';
const GPS_LEGAL_SLUG_TERMS   = 'termos-de-uso';

add_action('init', function (): void {
    if (get_option('gps_legal_pages_v1')) {
        return;
    }

    $pages = [
        GPS_LEGAL_SLUG_PRIVACY => ['Política de Privacidade', gps_legal_privacy_html()],
        GPS_LEGAL_SLUG_TERMS   => ['Termos de Uso',           gps_legal_terms_html()],
    ];

    foreach ($pages as $slug => [$title, $html]) {
        if (get_page_by_path($slug)) {
            continue;
        }
        wp_insert_post([
            'post_type'    => 'page',
            'post_status'  => 'draft',
            'post_name'    => $slug,
            'post_title'   => $title,
            'post_content' => $html,
        ]);
    }

    update_option('gps_legal_pages_v1', gmdate('c'));
});

/** URL da página legal se estiver PUBLICADA; senão string vazia. */
function gps_legal_url(string $slug): string
{
    $page = get_page_by_path($slug);
    return ($page && $page->post_status === 'publish') ? get_permalink($page) : '';
}

function gps_legal_privacy_html(): string
{
    $mark = static fn(string $t): string => '<mark>[PREENCHER: ' . $t . ']</mark>';
    $controlador = $mark('razão social ou nome, CNPJ/CPF, endereço');
    $email_dpo   = $mark('e-mail do encarregado/canal de privacidade');

    return <<<HTML
<p><em>Última atualização: {$mark('data')}.</em></p>

<p>Esta Política explica como a <strong>devspace</strong> trata dados pessoais, em conformidade com a Lei Geral de Proteção de Dados (Lei nº 13.709/2018 — LGPD).</p>

<h2>1. Quem é o controlador</h2>
<p>Controlador: {$controlador}. Contato sobre privacidade: {$email_dpo}.</p>

<h2>2. Quais dados tratamos e para quê</h2>
<table>
<thead><tr><th>Situação</th><th>Dados</th><th>Finalidade</th><th>Base legal (art. 7º)</th></tr></thead>
<tbody>
<tr><td>Formulário de contato / orçamento / suporte do site</td><td>Nome, e-mail, telefone (opcional), mensagem</td><td>Responder sua solicitação, enviar proposta e prestar suporte</td><td>Procedimentos preliminares a contrato e execução de contrato (V); legítimo interesse (IX) para suporte</td></tr>
<tr><td>Área do cliente (painel)</td><td>Nome, e-mail, telefone, projetos, chamados, contratos (com data, hora e IP do aceite), faturas, arquivos entregues</td><td>Executar o contrato, comprovar o aceite, cobrar e dar suporte</td><td>Execução de contrato (V); cumprimento de obrigação legal (II); exercício regular de direitos (VI)</td></tr>
<tr><td>Prospecção comercial (empresas locais)</td><td>Nome do estabelecimento, telefone comercial, avaliações públicas e demais informações públicas do negócio</td><td>Oferecer, de forma pontual, serviços de criação de sites e sistemas a empresas que não têm site</td><td>Legítimo interesse (IX), com avaliação documentada e opção de descadastro a qualquer momento</td></tr>
<tr><td>Navegação no site</td><td>Dados técnicos básicos (IP, navegador, páginas) em logs do servidor; preferência de tema e aviso de cookies no seu navegador</td><td>Segurança, funcionamento e melhoria do site</td><td>Legítimo interesse (IX)</td></tr>
</tbody>
</table>

<h2>3. Como obtemos dados de empresas (prospecção)</h2>
<p>Identificamos estabelecimentos por meio de informações públicas, como o Google Maps. Usamos o contato comercial apenas para apresentar nossos serviços. <strong>Para não receber mais contatos, basta responder SAIR à mensagem</strong> ou escrever para {$email_dpo}. Mantemos seu contato em uma lista de supressão apenas para não procurar você novamente.</p>

<h2>4. Com quem compartilhamos</h2>
<p>Usamos prestadores que tratam dados em nosso nome (operadores): hospedagem e infraestrutura ({$mark('provedor')}), serviços do Google (Google Maps Platform, para identificar estabelecimentos), Groq (geração de texto de apoio à redação de mensagens comerciais; recebe o nome do estabelecimento, avaliações públicas e anotações do lead, nunca dados de clientes já contratados), e meios de comunicação como o WhatsApp. Alguns desses prestadores podem processar dados fora do Brasil, com salvaguardas previstas na LGPD (art. 33). Não vendemos dados pessoais.</p>

<h2>5. Por quanto tempo guardamos</h2>
<ul>
<li>Mensagens de contato sem contratação: até {$mark('sugestão: 12 meses')} após a última interação.</li>
<li>Prospectos que não responderam: até {$mark('sugestão: 12 meses')} após o primeiro contato; depois, excluídos ou anonimizados (mantendo apenas o necessário para respeitar pedidos de descadastro).</li>
<li>Dados de clientes e contratos: durante a relação e pelo prazo necessário para obrigações legais e defesa em processos ({$mark('sugestão: 5 anos')}).</li>
</ul>

<h2>6. Seus direitos (art. 18 da LGPD)</h2>
<p>Você pode solicitar: confirmação de tratamento; acesso; correção; anonimização, bloqueio ou eliminação de dados desnecessários; portabilidade; informação sobre compartilhamento; e revogação de consentimento, quando aplicável. Também pode se opor a tratamentos baseados em legítimo interesse. Escreva para {$email_dpo}; respondemos em até {$mark('sugestão: 15 dias')}. Você também pode reclamar à Autoridade Nacional de Proteção de Dados (ANPD).</p>

<h2>7. Cookies e armazenamento local</h2>
<p>Este site usa apenas armazenamento essencial/funcional no seu navegador (por exemplo, lembrar o tema claro/escuro e que você viu o aviso de cookies). Não usamos cookies de publicidade nem ferramentas de análise de terceiros. Se isso mudar, atualizaremos esta Política e o aviso.</p>

<h2>8. Segurança</h2>
<p>Adotamos medidas técnicas e administrativas razoáveis (controle de acesso, senhas protegidas por hash, backups e proteção contra abuso nos formulários). Nenhum sistema é 100% seguro; em caso de incidente relevante, comunicaremos os afetados e a ANPD conforme a lei.</p>

<h2>9. Alterações</h2>
<p>Podemos atualizar esta Política. A data de atualização fica no topo da página.</p>
HTML;
}

function gps_legal_terms_html(): string
{
    $mark = static fn(string $t): string => '<mark>[PREENCHER: ' . $t . ']</mark>';

    return <<<HTML
<p><em>Última atualização: {$mark('data')}.</em></p>

<h2>1. Quem somos</h2>
<p>Este site é mantido pela <strong>devspace</strong> ({$mark('razão social, CNPJ/CPF')}), que presta serviços de criação de sites, aplicativos e sistemas.</p>

<h2>2. Uso do site</h2>
<p>O conteúdo é informativo. Você concorda em não usar o site para fins ilícitos, não tentar acessar áreas restritas, não enviar spam ou conteúdo malicioso pelos formulários e não interferir na segurança ou no funcionamento do serviço.</p>

<h2>3. Orçamentos e contratação</h2>
<p>Mensagens enviadas pelo formulário não criam, por si só, contrato. A contratação ocorre por proposta e contrato próprios, aceitos digitalmente na área do cliente, com registro de data, hora e IP do aceite.</p>

<h2>4. Área do cliente</h2>
<p>O acesso é pessoal e intransferível. Você é responsável por manter sua senha em sigilo e por comunicar qualquer uso indevido.</p>

<h2>5. Propriedade intelectual</h2>
<p>Marcas, textos e materiais deste site pertencem à devspace ou a seus licenciantes. Os direitos sobre entregas feitas a clientes seguem o que estiver previsto no respectivo contrato.</p>

<h2>6. Limitação de responsabilidade</h2>
<p>Empenhamo-nos para manter o site disponível e correto, mas ele é oferecido no estado em que se encontra, sem garantia de disponibilidade ininterrupta. {$mark('cláusula de limitação a validar com advogado')}</p>

<h2>7. Privacidade</h2>
<p>O tratamento de dados pessoais é descrito na <a href="/politica-de-privacidade">Política de Privacidade</a>.</p>

<h2>8. Lei aplicável e foro</h2>
<p>Estes Termos são regidos pelas leis do Brasil. Fica eleito o foro da comarca de {$mark('cidade/UF')}, ressalvado o foro do consumidor quando aplicável.</p>
HTML;
}
