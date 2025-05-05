<?php
if (!defined('ABSPATH')) {
    exit;
}

// Registrar o shortcode
function turmas_pilates_shortcode() {
    ob_start();
    
    // Gerar um nonce fresco a cada carregamento
    $nonce = wp_create_nonce('turmas_pilates_nonce');
    
    // Obter todos os estados que possuem turmas
    $estados_com_turmas = array();
    
    // Primeiro pegamos todos os posts do tipo 'turma'
    $turmas = get_posts(array(
        'post_type' => 'turma',
        'posts_per_page' => -1
    ));
    
    // Para cada turma, verificamos o estado
    foreach ($turmas as $turma) {
        $estados_da_turma = wp_get_post_terms($turma->ID, 'estado', array('fields' => 'ids'));
        foreach ($estados_da_turma as $estado_id) {
            if (!in_array($estado_id, $estados_com_turmas)) {
                $estados_com_turmas[] = $estado_id;
            }
        }
    }
    
    // Agora pegamos todos os detalhes desses estados
    $estados = array();
    if (!empty($estados_com_turmas)) {
        $estados = get_terms(array(
            'taxonomy' => 'estado',
            'include' => $estados_com_turmas,
            'hide_empty' => false,
        ));
    }
    
    // Carregar scripts e estilos na ordem correta
    wp_enqueue_style('turmas-pilates-frontend', TURMAS_PILATES_PLUGIN_URL . 'css/frontend.css');
    wp_enqueue_script('turmas-pilates-frontend', TURMAS_PILATES_PLUGIN_URL . 'js/frontend.js', array('jquery'), TURMAS_PILATES_VERSION, true);
    
    // Localizar o script com o nonce gerado
    wp_localize_script('turmas-pilates-frontend', 'turmasPilates', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce' => $nonce,
        'version' => TURMAS_PILATES_VERSION,
        'debug' => WP_DEBUG
    ));
    
    // Output HTML
    ?>
    <div class="turmas-filtro">
        <select id="turmas-estado">
            <option value="">Selecione o Estado</option>
            <?php foreach ($estados as $estado): ?>
                <option value="<?php echo esc_attr($estado->term_id); ?>">
                    <?php echo esc_html($estado->name); ?>
                </option>
            <?php endforeach; ?>
        </select>
        
        <select id="turmas-cidade">
            <option value="">Selecione a Cidade</option>
        </select>
        
        <!-- Debug info (visível apenas em modo de debug) -->
        <?php if (WP_DEBUG): ?>
        <div class="turmas-debug-info" style="display:none;">
            <p>Nonce: <span id="turmas-nonce"><?php echo esc_html($nonce); ?></span></p>
        </div>
        <?php endif; ?>
    </div>
    
    <div id="turmas-resultado"></div>
    <?php
    
    return ob_get_clean();
}
add_shortcode('turmas_pilates', 'turmas_pilates_shortcode');

// Funções AJAX
function turmas_pilates_get_cidades() {
    // Log para debug
    error_log('Requisição recebida para turmas_pilates_get_cidades');
    
    // Verificar nonce - remover mensagem de erro específica para segurança
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'turmas_pilates_nonce')) {
        error_log('Falha na verificação do nonce');
        error_log('Nonce recebido: ' . (isset($_POST['nonce']) ? $_POST['nonce'] : 'não definido'));
        wp_send_json_error('Verificação de segurança falhou');
        return;
    }
    
    if (!isset($_POST['estado_id']) || empty($_POST['estado_id'])) {
        error_log('ID do estado não fornecido');
        wp_send_json_error('ID do estado não fornecido');
        return;
    }
    
    $estado_id = intval($_POST['estado_id']);
    error_log('Buscando cidades para o estado ID: ' . $estado_id);
    
    $cidades = get_posts(array(
        'post_type' => 'turma',
        'tax_query' => array(
            array(
                'taxonomy' => 'estado',
                'field' => 'term_id',
                'terms' => $estado_id
            )
        ),
        'posts_per_page' => -1,
        'orderby' => 'title',
        'order' => 'ASC'
    ));
    
    $cidades_array = array();
    foreach ($cidades as $cidade) {
        $cidades_array[$cidade->ID] = $cidade->post_title;
    }
    
    error_log('Cidades encontradas: ' . count($cidades_array));
    wp_send_json_success($cidades_array);
}
add_action('wp_ajax_turmas_pilates_get_cidades', 'turmas_pilates_get_cidades');
add_action('wp_ajax_nopriv_turmas_pilates_get_cidades', 'turmas_pilates_get_cidades');

function turmas_pilates_get_turmas() {
    // Verificar nonce - remover mensagem de erro específica para segurança
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'turmas_pilates_nonce')) {
        wp_send_json_error('Verificação de segurança falhou');
        return;
    }
    
    $cidade_id = intval($_POST['cidade_id']);
    
    // Buscar as turmas
    $turmas = get_post_meta($cidade_id, '_turmas_pilates_turmas', true);
    $turmas_meio_semana = get_post_meta($cidade_id, '_turmas_pilates_meio_semana', true);
    
    // Buscar informações de local e contato
    $info_fds = get_post_meta($cidade_id, '_turmas_pilates_info_fds', true);
    $info_meio_semana = get_post_meta($cidade_id, '_turmas_pilates_info_meio_semana', true);
    
    // Verificar se os arrays existem
    if (!is_array($turmas)) {
        $turmas = array();
    }
    
    if (!is_array($turmas_meio_semana)) {
        $turmas_meio_semana = array();
    }
    
    // Adicionar tipo para identificar cada turma
    foreach ($turmas as &$turma) {
        $turma['tipo'] = 'fds'; // Final de semana
    }
    
    foreach ($turmas_meio_semana as &$turma) {
        $turma['tipo'] = 'semana'; // Durante a semana
    }
    
    // Criar array final com todas as turmas
    $todas_turmas = array(
        'final_semana' => $turmas,
        'meio_semana' => $turmas_meio_semana,
        'info_adicional' => array(
            'info_fds' => $info_fds,
            'info_meio_semana' => $info_meio_semana
        )
    );
    
    wp_send_json_success($todas_turmas);
}
add_action('wp_ajax_turmas_pilates_get_turmas', 'turmas_pilates_get_turmas');
add_action('wp_ajax_nopriv_turmas_pilates_get_turmas', 'turmas_pilates_get_turmas'); 