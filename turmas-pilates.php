<?php
/**
 * Plugin Name: Turmas de Pilates
 * Plugin URI: 
 * Description: Plugin para gerenciamento de turmas de cursos de pilates
 * Version: 22
 * Author: Weber
 * Author URI: 
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: turmas-pilates
 */

if (!defined('ABSPATH')) {
    exit;
}

// Definir constantes
define('TURMAS_PILATES_VERSION', '22');
define('TURMAS_PILATES_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('TURMAS_PILATES_PLUGIN_URL', plugin_dir_url(__FILE__));

// Incluir arquivos necessários
require_once TURMAS_PILATES_PLUGIN_DIR . 'shortcode.php';

// Registrar estados padrão na ativação do plugin
function turmas_pilates_activate() {
    // Lista de estados brasileiros
    $estados = array(
        'AC' => 'Acre',
        'AL' => 'Alagoas',
        'AP' => 'Amapá',
        'AM' => 'Amazonas',
        'BA' => 'Bahia',
        'CE' => 'Ceará',
        'DF' => 'Distrito Federal',
        'ES' => 'Espírito Santo',
        'GO' => 'Goiás',
        'MA' => 'Maranho',
        'MT' => 'Mato Grosso',
        'MS' => 'Mato Grosso do Sul',
        'MG' => 'Minas Gerais',
        'PA' => 'Pará',
        'PB' => 'Paraíba',
        'PR' => 'Paraná',
        'PE' => 'Pernambuco',
        'PI' => 'Piauí',
        'RJ' => 'Rio de Janeiro',
        'RN' => 'Rio Grande do Norte',
        'RS' => 'Rio Grande do Sul',
        'RO' => 'Rondônia',
        'RR' => 'Roraima',
        'SC' => 'Santa Catarina',
        'SP' => 'São Paulo',
        'SE' => 'Sergipe',
        'TO' => 'Tocantins'
    );
    
    // Garantir que a taxonomia está registrada
    turmas_pilates_register_taxonomy();
    
    // Criar cada estado como um termo da taxonomia
    foreach ($estados as $sigla => $nome) {
        // Verificar se o estado já existe para evitar duplicatas
        if (!term_exists($nome, 'estado')) {
            wp_insert_term(
                $nome, 
                'estado',
                array(
                    'slug' => sanitize_title($sigla),
                    'description' => 'Estado do ' . $nome
                )
            );
        }
    }
}
register_activation_hook(__FILE__, 'turmas_pilates_activate');

// Registrar Custom Post Type
function turmas_pilates_register_post_type() {
    $labels = array(
        'name'               => 'Turmas',
        'singular_name'      => 'Turma',
        'menu_name'          => 'Turmas',
        'add_new'           => 'Adicionar Nova',
        'add_new_item'      => 'Adicionar Nova Turma',
        'edit_item'         => 'Editar Turma',
        'new_item'          => 'Nova Turma',
        'view_item'         => 'Ver Turma',
        'search_items'      => 'Buscar Turmas',
        'not_found'         => 'Nenhuma turma encontrada',
        'not_found_in_trash'=> 'Nenhuma turma encontrada na lixeira'
    );

    $args = array(
        'labels'              => $labels,
        'public'              => true,
        'has_archive'         => true,
        'publicly_queryable'  => true,
        'query_var'           => true,
        'rewrite'             => array('slug' => 'turmas'),
        'capability_type'     => 'post',
        'hierarchical'        => false,
        'supports'            => array('title', 'revisions'),
        'menu_position'       => 5,
        'menu_icon'           => 'dashicons-groups'
    );

    register_post_type('turma', $args);
}
add_action('init', 'turmas_pilates_register_post_type');

// Registrar taxonomia para Estados
function turmas_pilates_register_taxonomy() {
    $labels = array(
        'name'              => 'Estados',
        'singular_name'     => 'Estado',
        'search_items'      => 'Buscar Estados',
        'all_items'         => 'Todos os Estados',
        'edit_item'         => 'Editar Estado',
        'update_item'       => 'Atualizar Estado',
        'add_new_item'      => 'Adicionar Novo Estado',
        'new_item_name'     => 'Novo Nome do Estado',
        'menu_name'         => 'Estados'
    );

    $args = array(
        'hierarchical'      => true,
        'labels'            => $labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array('slug' => 'estado')
    );

    register_taxonomy('estado', 'turma', $args);
}
add_action('init', 'turmas_pilates_register_taxonomy');

// Adicionar campos personalizados
function turmas_pilates_add_meta_boxes() {
    add_meta_box(
        'turmas_pilates_meta_box',
        'Turmas de Final de Semana',
        'turmas_pilates_meta_box_callback',
        'turma',
        'normal',
        'high'
    );
    
    add_meta_box(
        'turmas_pilates_meio_semana_meta_box',
        'Turmas Durante a Semana',
        'turmas_pilates_meio_semana_meta_box_callback',
        'turma',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'turmas_pilates_add_meta_boxes');

// Adicionar página de ajuda
function turmas_pilates_add_menu_pages() {
    add_submenu_page(
        'edit.php?post_type=turma',
        'Instruções de Uso',
        'Instruções de Uso',
        'manage_options',
        'turmas-pilates-help',
        'turmas_pilates_help_page_callback'
    );
}
add_action('admin_menu', 'turmas_pilates_add_menu_pages');

// Callback para a pgina de ajuda
function turmas_pilates_help_page_callback() {
    ?>
    <div class="wrap">
        <h1>Instruções de Uso - Plugin Turmas de Pilates</h1>
        
        <div class="turmas-instructions">
            <h2>Como usar este plugin</h2>
            
            <div class="instructions-section">
                <h3>Passo 1: Cadastro de Turmas</h3>
                <ol>
                    <li>Crie um post do tipo "Turma" para cada cidade</li>
                    <li>No título, coloque o nome da cidade</li>
                    <li>Selecione o estado correspondente na caixa "Estados" à direita</li>
                    <li>Preencha as turmas de final de semana e/ou durante a semana conforme necessário</li>
                    <li>Adicione informações sobre local e contatos em cada seção</li>
                </ol>
            </div>
            
            <div class="instructions-section">
                <h3>Passo 2: Exibindo no Frontend</h3>
                <p>Para exibir o filtro e as turmas em qualquer página ou post, utilize o shortcode:</p>
                <div class="shortcode-highlight-container">
                    <code class="shortcode-highlight">[turmas_pilates]</code>
                    <button class="copy-shortcode" onclick="copyShortcode()" title="Copiar Shortcode">Copiar</button>
                </div>
                <p>Este shortcode irá exibir:</p>
                <ul>
                    <li>Um seletor de estados</li>
                    <li>Um seletor de cidades (preenchido automaticamente com base no estado selecionado)</li>
                    <li>Turmas cadastradas para a cidade selecionada</li>
                    <li>Informações de local/contato para cada tipo de turma</li>
                </ul>
            </div>
            
            <div class="instructions-section">
                <h3>Funcionalidades Disponíveis</h3>
                <ul>
                    <li><strong>Organização:</strong> Turmas separadas por final de semana e meio de semana</li>
                    <li><strong>Status das Vagas:</strong> Abertas, Esgotadas ou Restantes (com contador)</li>
                    <li><strong>Filtro dinâmico:</strong> Usuários podem encontrar turmas por estado e cidade</li>
                    <li><strong>Informações adicionais:</strong> Campo para adicionar informações sobre local e contato</li>
                </ul>
            </div>
        </div>
    </div>
    <script>
    function copyShortcode() {
        var shortcode = document.querySelector('.shortcode-highlight');
        var range = document.createRange();
        range.selectNode(shortcode);
        window.getSelection().removeAllRanges();
        window.getSelection().addRange(range);
        document.execCommand('copy');
        window.getSelection().removeAllRanges();
        
        var button = document.querySelector('.copy-shortcode');
        var originalText = button.textContent;
        button.textContent = 'Copiado!';
        setTimeout(function() {
            button.textContent = originalText;
        }, 2000);
    }
    </script>
    <style>
    .turmas-instructions {
        background-color: #fff;
        padding: 20px;
        margin-top: 20px;
        border-radius: 5px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    
    .instructions-section {
        margin-bottom: 30px;
        padding-bottom: 20px;
        border-bottom: 1px solid #eee;
    }
    
    .instructions-section:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }
    
    .instructions-section h3 {
        color: #23282d;
        font-size: 18px;
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 1px solid #eee;
    }
    
    .shortcode-highlight-container {
        display: flex;
        align-items: center;
        margin: 20px 0;
    }
    
    .shortcode-highlight {
        display: inline-block;
        background-color: #f0f0f1;
        font-family: Consolas, Monaco, monospace;
        padding: 10px 15px;
        border: 1px solid #ddd;
        border-radius: 3px 0 0 3px;
        font-size: 16px;
        color: #3c434a;
        font-weight: bold;
        margin: 0;
    }
    
    .copy-shortcode {
        background-color: #007cba;
        color: white;
        border: none;
        padding: 10px 15px;
        border-radius: 0 3px 3px 0;
        cursor: pointer;
        font-size: 14px;
        transition: background 0.3s;
    }
    
    .copy-shortcode:hover {
        background-color: #006ba1;
    }
    </style>
    <?php
}

// Callback para o meta box de final de semana
function turmas_pilates_meta_box_callback($post) {
    wp_nonce_field('turmas_pilates_meta_box', 'turmas_pilates_meta_box_nonce');
    
    $turmas = get_post_meta($post->ID, '_turmas_pilates_turmas', true);
    if (!is_array($turmas)) {
        $turmas = array();
    }
    
    // Obter informações do local/contato
    $info_fds = get_post_meta($post->ID, '_turmas_pilates_info_fds', true);
    ?>
    <div id="turmas-container">
        <?php foreach ($turmas as $index => $turma): ?>
        <div class="turma-item">
            <h4>Turma <?php echo $index + 1; ?></h4>
            <p>
                <label>Número da Turma:</label>
                <input type="text" name="turmas[<?php echo $index; ?>][numero]" value="<?php echo esc_attr($turma['numero']); ?>">
            </p>
            <p>
                <label>Datas do Mdulo:</label>
                <div class="date-inputs-row">
                    <div class="date-input-container">
                        <input type="text" name="turmas[<?php echo $index; ?>][modulo_data1]" value="<?php echo isset($turma['modulo_data1']) ? esc_attr($turma['modulo_data1']) : ''; ?>" class="single-date-field" placeholder="Primeira data" readonly>
                        <span class="date-icon dashicons dashicons-calendar-alt"></span>
                    </div>
                    <span class="date-separator">e</span>
                    <div class="date-input-container">
                        <input type="text" name="turmas[<?php echo $index; ?>][modulo_data2]" value="<?php echo isset($turma['modulo_data2']) ? esc_attr($turma['modulo_data2']) : ''; ?>" class="single-date-field" placeholder="Segunda data" readonly>
                        <span class="date-icon dashicons dashicons-calendar-alt"></span>
                    </div>
                    <span class="date-separator">e</span>
                    <div class="date-input-container">
                        <input type="text" name="turmas[<?php echo $index; ?>][modulo_data3]" value="<?php echo isset($turma['modulo_data3']) ? esc_attr($turma['modulo_data3']) : ''; ?>" class="single-date-field" placeholder="Terceira data" readonly>
                        <span class="date-icon dashicons dashicons-calendar-alt"></span>
                    </div>
                </div>
            </p>
            <p>
                <label>Status das Vagas:</label>
                <select name="turmas[<?php echo $index; ?>][status]" class="status-select">
                    <option value="abertas" <?php selected($turma['status'], 'abertas'); ?>>Vagas Abertas</option>
                    <option value="esgotadas" <?php selected($turma['status'], 'esgotadas'); ?>>Vagas Esgotadas</option>
                    <option value="restantes" <?php selected($turma['status'], 'restantes'); ?>>Vagas Restantes</option>
                </select>
            </p>
            <p class="quantidade-vagas-container" style="<?php echo $turma['status'] === 'restantes' ? '' : 'display:none;'; ?>">
                <label>Quantidade de Vagas Restantes:</label>
                <input type="number" name="turmas[<?php echo $index; ?>][qtd_vagas]" value="<?php echo isset($turma['qtd_vagas']) ? esc_attr($turma['qtd_vagas']) : ''; ?>">
            </p>
            <button type="button" class="remove-turma">Remover Turma</button>
        </div>
        <?php endforeach; ?>
    </div>
    <button type="button" id="add-turma" class="add-turma-button">Adicionar Nova Turma de Final de Semana</button>
    
    <div class="location-contact-section">
        <h4>Informações de Local e Contato</h4>
        <p>
            <label>Informações (Final de Semana):</label>
            <?php 
            wp_editor(
                $info_fds,
                'info_fds',
                array(
                    'textarea_name' => 'info_fds',
                    'textarea_rows' => 8,
                    'media_buttons' => true,
                    'teeny' => false,
                    'quicktags' => true,
                    'tinymce' => array(
                        'toolbar1' => 'formatselect,bold,italic,underline,strikethrough,bullist,numlist,blockquote,hr,alignleft,aligncenter,alignright,link,unlink,forecolor,pastetext,removeformat,charmap,outdent,indent,undo,redo,wp_help',
                        'toolbar2' => ''
                    )
                )
            );
            ?>
            <span class="description">Insira informações sobre local e contato. Use as ferramentas de formataço para melhor organização do texto.</span>
        </p>
    </div>
    
    <script>
    jQuery(document).ready(function($) {
        $('#add-turma').on('click', function() {
            var index = $('#turmas-container .turma-item').length;
            var html = `
                <div class="turma-item">
                    <h4>Turma ${index + 1}</h4>
                    <p>
                        <label>Nmero da Turma:</label>
                        <input type="text" name="turmas[${index}][numero]">
                    </p>
                    <p>
                        <label>Datas do Módulo:</label>
                        <div class="date-inputs-row">
                            <div class="date-input-container">
                                <input type="text" name="turmas[${index}][modulo_data1]" class="single-date-field" placeholder="Primeira data" readonly>
                                <span class="date-icon dashicons dashicons-calendar-alt"></span>
                            </div>
                            <span class="date-separator">e</span>
                            <div class="date-input-container">
                                <input type="text" name="turmas[${index}][modulo_data2]" class="single-date-field" placeholder="Segunda data" readonly>
                                <span class="date-icon dashicons dashicons-calendar-alt"></span>
                            </div>
                            <span class="date-separator">e</span>
                            <div class="date-input-container">
                                <input type="text" name="turmas[${index}][modulo_data3]" class="single-date-field" placeholder="Terceira data" readonly>
                                <span class="date-icon dashicons dashicons-calendar-alt"></span>
                            </div>
                        </div>
                    </p>
                    <p>
                        <label>Status das Vagas:</label>
                        <select name="turmas[${index}][status]" class="status-select">
                            <option value="abertas">Vagas Abertas</option>
                            <option value="esgotadas">Vagas Esgotadas</option>
                            <option value="restantes">Vagas Restantes</option>
                        </select>
                    </p>
                    <p class="quantidade-vagas-container" style="display:none;">
                        <label>Quantidade de Vagas Restantes:</label>
                        <input type="number" name="turmas[${index}][qtd_vagas]">
                    </p>
                    <button type="button" class="remove-turma">Remover Turma</button>
                </div>
            `;
            $('#turmas-container').append(html);
        });

        $(document).on('click', '.remove-turma', function() {
            $(this).parent().remove();
        });
        
        // Mostrar/esconder campo de quantidade de vagas
        $(document).on('change', '.status-select', function() {
            var qtdContainer = $(this).closest('p').next('.quantidade-vagas-container');
            if ($(this).val() === 'restantes') {
                qtdContainer.show();
            } else {
                qtdContainer.hide();
                // Limpar o valor do campo quando não for "restantes"
                qtdContainer.find('input[type="number"]').val('');
            }
        });

        // Ativar eventos para turmas já existentes
        $('.status-select').each(function() {
            if ($(this).val() === 'restantes') {
                $(this).closest('p').next('.quantidade-vagas-container').show();
            } else {
                $(this).closest('p').next('.quantidade-vagas-container').hide();
            }
        });
    });
    </script>
    <?php
}

// Callback para o meta box de meio de semana
function turmas_pilates_meio_semana_meta_box_callback($post) {
    wp_nonce_field('turmas_pilates_meio_semana_meta_box', 'turmas_pilates_meio_semana_meta_box_nonce');
    
    $turmas_meio_semana = get_post_meta($post->ID, '_turmas_pilates_meio_semana', true);
    if (!is_array($turmas_meio_semana)) {
        $turmas_meio_semana = array();
    }
    
    // Obter informações do local/contato
    $info_meio_semana = get_post_meta($post->ID, '_turmas_pilates_info_meio_semana', true);
    ?>
    <div id="turmas-meio-semana-container">
        <?php foreach ($turmas_meio_semana as $index => $turma): ?>
        <div class="turma-item turma-meio-semana-item">
            <h4>Turma <?php echo $index + 1; ?></h4>
            <p>
                <label>Número da Turma:</label>
                <input type="text" name="turmas_meio_semana[<?php echo $index; ?>][numero]" value="<?php echo esc_attr($turma['numero']); ?>">
            </p>
            <p>
                <label>Datas do Módulo:</label>
                <div class="date-inputs-row">
                    <div class="date-input-container">
                        <input type="text" name="turmas_meio_semana[<?php echo $index; ?>][modulo_data1]" value="<?php echo isset($turma['modulo_data1']) ? esc_attr($turma['modulo_data1']) : ''; ?>" class="single-date-field" placeholder="Primeira data" readonly>
                        <span class="date-icon dashicons dashicons-calendar-alt"></span>
                    </div>
                    <span class="date-separator">e</span>
                    <div class="date-input-container">
                        <input type="text" name="turmas_meio_semana[<?php echo $index; ?>][modulo_data2]" value="<?php echo isset($turma['modulo_data2']) ? esc_attr($turma['modulo_data2']) : ''; ?>" class="single-date-field" placeholder="Segunda data" readonly>
                        <span class="date-icon dashicons dashicons-calendar-alt"></span>
                    </div>
                    <span class="date-separator">e</span>
                    <div class="date-input-container">
                        <input type="text" name="turmas_meio_semana[<?php echo $index; ?>][modulo_data3]" value="<?php echo isset($turma['modulo_data3']) ? esc_attr($turma['modulo_data3']) : ''; ?>" class="single-date-field" placeholder="Terceira data" readonly>
                        <span class="date-icon dashicons dashicons-calendar-alt"></span>
                    </div>
                </div>
            </p>
            <p>
                <label>Status das Vagas:</label>
                <select name="turmas_meio_semana[<?php echo $index; ?>][status]" class="status-select">
                    <option value="abertas" <?php selected($turma['status'], 'abertas'); ?>>Vagas Abertas</option>
                    <option value="esgotadas" <?php selected($turma['status'], 'esgotadas'); ?>>Vagas Esgotadas</option>
                    <option value="restantes" <?php selected($turma['status'], 'restantes'); ?>>Vagas Restantes</option>
                </select>
            </p>
            <p class="quantidade-vagas-container" style="<?php echo $turma['status'] === 'restantes' ? '' : 'display:none;'; ?>">
                <label>Quantidade de Vagas Restantes:</label>
                <input type="number" name="turmas_meio_semana[<?php echo $index; ?>][qtd_vagas]" value="<?php echo isset($turma['qtd_vagas']) ? esc_attr($turma['qtd_vagas']) : ''; ?>">
            </p>
            <button type="button" class="remove-turma">Remover Turma</button>
        </div>
        <?php endforeach; ?>
    </div>
    <button type="button" id="add-turma-meio-semana" class="add-turma-button">Adicionar Nova Turma Durante a Semana</button>
    
    <div class="location-contact-section">
        <h4>Informações de Local e Contato</h4>
        <p>
            <label>Informações (Durante a Semana):</label>
            <?php 
            wp_editor(
                $info_meio_semana,
                'info_meio_semana',
                array(
                    'textarea_name' => 'info_meio_semana',
                    'textarea_rows' => 8,
                    'media_buttons' => true,
                    'teeny' => false,
                    'quicktags' => true,
                    'tinymce' => array(
                        'toolbar1' => 'formatselect,bold,italic,underline,strikethrough,bullist,numlist,blockquote,hr,alignleft,aligncenter,alignright,link,unlink,forecolor,pastetext,removeformat,charmap,outdent,indent,undo,redo,wp_help',
                        'toolbar2' => ''
                    )
                )
            );
            ?>
            <span class="description">Insira informações sobre local e contato. Use as ferramentas de formatação para melhor organização do texto.</span>
        </p>
    </div>
    
    <script>
    jQuery(document).ready(function($) {
        $('#add-turma-meio-semana').on('click', function() {
            var index = $('#turmas-meio-semana-container .turma-meio-semana-item').length;
            var html = `
                <div class="turma-item turma-meio-semana-item">
                    <h4>Turma ${index + 1}</h4>
                    <p>
                        <label>Número da Turma:</label>
                        <input type="text" name="turmas_meio_semana[${index}][numero]">
                    </p>
                    <p>
                        <label>Datas do Módulo:</label>
                        <div class="date-inputs-row">
                            <div class="date-input-container">
                                <input type="text" name="turmas_meio_semana[${index}][modulo_data1]" class="single-date-field" placeholder="Primeira data" readonly>
                                <span class="date-icon dashicons dashicons-calendar-alt"></span>
                            </div>
                            <span class="date-separator">e</span>
                            <div class="date-input-container">
                                <input type="text" name="turmas_meio_semana[${index}][modulo_data2]" class="single-date-field" placeholder="Segunda data" readonly>
                                <span class="date-icon dashicons dashicons-calendar-alt"></span>
                            </div>
                            <span class="date-separator">e</span>
                            <div class="date-input-container">
                                <input type="text" name="turmas_meio_semana[${index}][modulo_data3]" class="single-date-field" placeholder="Terceira data" readonly>
                                <span class="date-icon dashicons dashicons-calendar-alt"></span>
                            </div>
                        </div>
                    </p>
                    <p>
                        <label>Status das Vagas:</label>
                        <select name="turmas_meio_semana[${index}][status]" class="status-select">
                            <option value="abertas">Vagas Abertas</option>
                            <option value="esgotadas">Vagas Esgotadas</option>
                            <option value="restantes">Vagas Restantes</option>
                        </select>
                    </p>
                    <p class="quantidade-vagas-container" style="display:none;">
                        <label>Quantidade de Vagas Restantes:</label>
                        <input type="number" name="turmas_meio_semana[${index}][qtd_vagas]">
                    </p>
                    <button type="button" class="remove-turma">Remover Turma</button>
                </div>
            `;
            $('#turmas-meio-semana-container').append(html);
        });
        
        // Adicionar eventos para status-select neste container também
        $(document).on('change', '#turmas_pilates_meio_semana_meta_box .status-select', function() {
            var qtdContainer = $(this).closest('p').next('.quantidade-vagas-container');
            if ($(this).val() === 'restantes') {
                qtdContainer.show();
            } else {
                qtdContainer.hide();
                // Limpar o valor do campo quando não for "restantes"
                qtdContainer.find('input[type="number"]').val('');
            }
        });
        
        // Ativar eventos para turmas j existentes neste container
        $('#turmas_pilates_meio_semana_meta_box .status-select').each(function() {
            if ($(this).val() === 'restantes') {
                $(this).closest('p').next('.quantidade-vagas-container').show();
            } else {
                $(this).closest('p').next('.quantidade-vagas-container').hide();
            }
        });
    });
    </script>
    <?php
}

// Salvar os campos personalizados
function turmas_pilates_save_meta_box($post_id) {
    // Verificar salvamento automático
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    
    // Verificar permissões
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    
    // Salvar meta box de final de semana
    if (isset($_POST['turmas_pilates_meta_box_nonce']) && 
        wp_verify_nonce($_POST['turmas_pilates_meta_box_nonce'], 'turmas_pilates_meta_box')) {
        
        // Salvar turmas
        if (isset($_POST['turmas']) && is_array($_POST['turmas'])) {
            $turmas = array();
            
            foreach ($_POST['turmas'] as $index => $turma) {
                // Gerar número aleatório entre 8 e 12 se o status for 'restantes' e qtd_vagas estiver vazio
                $qtd_vagas = isset($turma['qtd_vagas']) ? intval($turma['qtd_vagas']) : 0;
                if ($turma['status'] === 'restantes' && $qtd_vagas <= 0) {
                    $qtd_vagas = rand(8, 12);
                }
                
                $turmas[$index] = array(
                    'numero' => isset($turma['numero']) ? sanitize_text_field($turma['numero']) : '',
                    'modulo_data1' => isset($turma['modulo_data1']) ? sanitize_text_field($turma['modulo_data1']) : '',
                    'modulo_data2' => isset($turma['modulo_data2']) ? sanitize_text_field($turma['modulo_data2']) : '',
                    'modulo_data3' => isset($turma['modulo_data3']) ? sanitize_text_field($turma['modulo_data3']) : '',
                    'status' => isset($turma['status']) ? sanitize_text_field($turma['status']) : 'abertas',
                    'qtd_vagas' => $qtd_vagas
                );
            }
            
            update_post_meta($post_id, '_turmas_pilates_turmas', $turmas);
        }
        
        // Salvar informações adicionais
        if (isset($_POST['info_fds'])) {
            update_post_meta($post_id, '_turmas_pilates_info_fds', wp_kses_post($_POST['info_fds']));
        }
    }
    
    // Salvar meta box de meio de semana
    if (isset($_POST['turmas_pilates_meio_semana_meta_box_nonce']) && 
        wp_verify_nonce($_POST['turmas_pilates_meio_semana_meta_box_nonce'], 'turmas_pilates_meio_semana_meta_box')) {
        
        // Salvar turmas
        if (isset($_POST['turmas_meio_semana']) && is_array($_POST['turmas_meio_semana'])) {
            $turmas_meio_semana = array();
            
            foreach ($_POST['turmas_meio_semana'] as $index => $turma) {
                // Gerar número aleatório entre 8 e 12 se o status for 'restantes' e qtd_vagas estiver vazio
                $qtd_vagas = isset($turma['qtd_vagas']) ? intval($turma['qtd_vagas']) : 0;
                if ($turma['status'] === 'restantes' && $qtd_vagas <= 0) {
                    $qtd_vagas = rand(8, 12);
                }
                
                $turmas_meio_semana[$index] = array(
                    'numero' => isset($turma['numero']) ? sanitize_text_field($turma['numero']) : '',
                    'modulo_data1' => isset($turma['modulo_data1']) ? sanitize_text_field($turma['modulo_data1']) : '',
                    'modulo_data2' => isset($turma['modulo_data2']) ? sanitize_text_field($turma['modulo_data2']) : '',
                    'modulo_data3' => isset($turma['modulo_data3']) ? sanitize_text_field($turma['modulo_data3']) : '',
                    'status' => isset($turma['status']) ? sanitize_text_field($turma['status']) : 'abertas',
                    'qtd_vagas' => $qtd_vagas
                );
            }
            
            update_post_meta($post_id, '_turmas_pilates_meio_semana', $turmas_meio_semana);
        }
        
        // Salvar informações adicionais
        if (isset($_POST['info_meio_semana'])) {
            update_post_meta($post_id, '_turmas_pilates_info_meio_semana', wp_kses_post($_POST['info_meio_semana']));
        }
    }
}
add_action('save_post', 'turmas_pilates_save_meta_box');

// Remover editor de blocos (Gutenberg) para o tipo de post 'turma'
function turmas_pilates_disable_gutenberg($is_enabled, $post_type) {
    if ($post_type === 'turma') {
        return false;
    }
    return $is_enabled;
}
add_filter('use_block_editor_for_post_type', 'turmas_pilates_disable_gutenberg', 10, 2);

// Adicionar estilos e scripts
function turmas_pilates_enqueue_scripts() {
    if (is_admin()) {
        // Estilos e scripts para o admin
        wp_enqueue_style('turmas-pilates-admin', TURMAS_PILATES_PLUGIN_URL . 'css/admin.css');
        wp_enqueue_script('turmas-pilates-admin', TURMAS_PILATES_PLUGIN_URL . 'js/admin.js', array('jquery'), TURMAS_PILATES_VERSION, true);
        
        // Carregar jQuery UI Datepicker apenas nas páginas de edição do nosso post type
        global $post_type;
        if ('turma' === $post_type) {
            // Dashicons
            wp_enqueue_style('dashicons');
            
            // jQuery UI
            wp_enqueue_style('jquery-ui', 'https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css');
            wp_enqueue_script('jquery-ui-datepicker');
            
            // Script personalizado para inicializar os datepickers
            wp_enqueue_script('turmas-pilates-datepicker', TURMAS_PILATES_PLUGIN_URL . 'js/datepicker.js', array('jquery', 'jquery-ui-datepicker'), TURMAS_PILATES_VERSION, true);
        }
    }
}
add_action('admin_enqueue_scripts', 'turmas_pilates_enqueue_scripts');

// Adicionar função para renovar nonce via AJAX
function turmas_pilates_refresh_nonce() {
    // Gerar um novo nonce
    $new_nonce = wp_create_nonce('turmas_pilates_nonce');
    error_log('Novo nonce gerado: ' . substr($new_nonce, 0, 3) . '...');
    
    // Enviar para o cliente
    wp_send_json_success($new_nonce);
}
add_action('wp_ajax_turmas_pilates_refresh_nonce', 'turmas_pilates_refresh_nonce');
add_action('wp_ajax_nopriv_turmas_pilates_refresh_nonce', 'turmas_pilates_refresh_nonce'); 