<?php
defined('ABSPATH') || exit;

/**
 * Shortcode: [gps_contact_form tipo="vendas|suporte|geral"]
 *
 * tipo="geral" → exibe seletor de motivo visível
 * tipo="vendas"|"suporte" → motivo pré-definido (campo oculto)
 */
add_shortcode('gps_contact_form', function (array $atts): string {
    $atts = shortcode_atts(['tipo' => 'geral'], $atts, 'gps_contact_form');
    $tipo = sanitize_key($atts['tipo']);
    $uid  = 'gps-form-' . $tipo . '-' . wp_rand(1000, 9999);

    ob_start();
    ?>
    <div class="gps-form-wrap" id="<?php echo esc_attr($uid); ?>">
        <form class="gps-form" novalidate autocomplete="off">

            <?php if ($tipo === 'geral'): ?>
            <div class="gps-form__group">
                <label for="<?php echo esc_attr($uid); ?>-tipo">Motivo do contato *</label>
                <select id="<?php echo esc_attr($uid); ?>-tipo" name="tipo" required>
                    <option value="">Selecione...</option>
                    <option value="vendas">Solicitar Orçamento</option>
                    <option value="suporte">Suporte Técnico</option>
                    <option value="geral">Outro assunto</option>
                </select>
            </div>
            <?php else: ?>
            <input type="hidden" name="tipo" value="<?php echo esc_attr($tipo); ?>">
            <?php endif; ?>

            <div class="gps-form__row gps-form__row--2">
                <div class="gps-form__group">
                    <label for="<?php echo esc_attr($uid); ?>-name">Nome completo *</label>
                    <input type="text" id="<?php echo esc_attr($uid); ?>-name"
                           name="name" placeholder="Seu nome" required>
                </div>
                <div class="gps-form__group">
                    <label for="<?php echo esc_attr($uid); ?>-phone">WhatsApp / Telefone</label>
                    <input type="tel" id="<?php echo esc_attr($uid); ?>-phone"
                           name="phone" placeholder="(11) 99999-9999">
                </div>
            </div>

            <div class="gps-form__group">
                <label for="<?php echo esc_attr($uid); ?>-email">E-mail *</label>
                <input type="email" id="<?php echo esc_attr($uid); ?>-email"
                       name="email" placeholder="seu@email.com.br" required>
            </div>

            <div class="gps-form__group">
                <label for="<?php echo esc_attr($uid); ?>-message">Mensagem *</label>
                <textarea id="<?php echo esc_attr($uid); ?>-message"
                          name="message" rows="5"
                          placeholder="Como podemos ajudar?" required></textarea>
            </div>

            <div class="gps-form__feedback" hidden></div>

            <button type="submit" class="gps-btn gps-btn--primary gps-btn--block">
                Enviar mensagem &rarr;
            </button>
            <p class="gps-form__privacy">
                🔒 Seus dados estão protegidos. Não enviamos spam.
            </p>
        </form>
    </div>

    <style>
    .gps-form-wrap { font-family: inherit; }
    .gps-form__row--2 { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
    @media (max-width: 540px) { .gps-form__row--2 { grid-template-columns: 1fr; } }
    .gps-form__group { display: flex; flex-direction: column; gap: 5px; margin-bottom: 16px; }
    .gps-form__group label { font-size: .8125rem; font-weight: 600; color: #374151; letter-spacing: .01em; text-transform: uppercase; }
    .gps-form__group input,
    .gps-form__group select,
    .gps-form__group textarea {
        padding: 11px 14px;
        border: 1.5px solid #e2e8f0;
        border-radius: 8px;
        font-size: .9375rem;
        color: #1e293b;
        font-family: inherit;
        background: #fff;
        transition: border-color .15s, box-shadow .15s;
        outline: none;
        width: 100%;
        box-sizing: border-box;
    }
    .gps-form__group input:focus,
    .gps-form__group select:focus,
    .gps-form__group textarea:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37,99,235,.12);
    }
    .gps-form__group textarea { resize: vertical; min-height: 120px; }
    .gps-form__group select { appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='%2364748b' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 14px center; padding-right: 36px; cursor: pointer; }
    .gps-btn { display: inline-flex; align-items: center; justify-content: center; gap: 6px; padding: 9px 20px; font-size: .9375rem; font-weight: 600; border-radius: 8px; text-decoration: none; cursor: pointer; border: none; font-family: inherit; transition: background .15s, opacity .15s; }
    .gps-btn--primary { background: #2563eb; color: #fff !important; }
    .gps-btn--primary:hover:not(:disabled) { background: #1d4ed8; }
    .gps-btn--primary:disabled { opacity: .65; cursor: not-allowed; }
    .gps-btn--block { width: 100%; padding: 13px; font-size: 1rem; }
    .gps-form__feedback { padding: 12px 16px; border-radius: 8px; font-size: .9rem; font-weight: 500; margin-bottom: 14px; }
    .gps-form__feedback.success { background: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0; }
    .gps-form__feedback.error   { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }
    .gps-form__privacy { font-size: .75rem; color: #94a3b8; text-align: center; margin: 10px 0 0; }
    </style>

    <script>
    (function () {
        var wrap = document.getElementById('<?php echo esc_js($uid); ?>');
        if (!wrap) return;
        var form = wrap.querySelector('.gps-form');
        var feed = wrap.querySelector('.gps-form__feedback');
        var sbmt = wrap.querySelector('[type="submit"]');

        form.addEventListener('submit', function (e) {
            e.preventDefault();
            var tipoEl = form.elements['tipo'];
            var tipoVal = tipoEl ? tipoEl.value.trim() : '';
            if (!tipoVal) {
                feed.textContent = 'Selecione o motivo do contato.';
                feed.className = 'gps-form__feedback error';
                feed.hidden = false;
                return;
            }
            sbmt.disabled = true;
            sbmt.textContent = 'Enviando...';
            feed.hidden = true;

            var cfg = (typeof gpsConfig !== 'undefined') ? gpsConfig : {};
            fetch(cfg.restUrl || '/wp-json/gps/v1/contact', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': cfg.nonce || '' },
                body: JSON.stringify({
                    name:    form.name.value.trim(),
                    email:   form.email.value.trim(),
                    phone:   form.phone.value.trim(),
                    message: form.message.value.trim(),
                    tipo:    tipoVal,
                }),
            })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                feed.textContent = data.message;
                feed.className = 'gps-form__feedback ' + (data.success ? 'success' : 'error');
                feed.hidden = false;
                if (data.success) form.reset();
            })
            .catch(function () {
                feed.textContent = 'Erro ao enviar. Tente novamente.';
                feed.className = 'gps-form__feedback error';
                feed.hidden = false;
            })
            .finally(function () {
                sbmt.disabled = false;
                sbmt.textContent = 'Enviar mensagem →';
            });
        });
    })();
    </script>
    <?php
    return ob_get_clean();
});
