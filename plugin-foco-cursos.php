<?php
/**
 * Plugin Name: Foco Digital Cursos - Gerenciador de Turmas
 * Plugin URI: https://agenciafocodigital.com.br
 * Description: Plugin customizado para a Foco Digital Cursos. Registra o Post Type de Cursos, campos do ACF para gerenciamento de turmas, e disponibiliza o shortcode [tabela_turmas] com renderização responsiva estilo M2BR Academy.
 * Version: 1.0.0
 * Author: Antigravity - Foco Digital
 * Author URI: https://agenciafocodigital.com.br
 * License: GPL2
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Segurança contra acesso direto
}

// 1. REGISTRO DO CUSTOM POST TYPE (CPT) - CURSOS
add_action( 'init', 'foco_register_cpt_cursos' );
function foco_register_cpt_cursos() {
    $labels = array(
        'name'               => _x( 'Cursos', 'post type general name', 'foco-digital' ),
        'singular_name'      => _x( 'Curso', 'post type singular name', 'foco-digital' ),
        'menu_name'          => _x( 'Cursos', 'admin menu', 'foco-digital' ),
        'name_admin_bar'     => _x( 'Curso', 'add new on admin bar', 'foco-digital' ),
        'add_new'            => _x( 'Adicionar Novo', 'curso', 'foco-digital' ),
        'add_new_item'       => __( 'Adicionar Novo Curso', 'foco-digital' ),
        'new_item'           => __( 'Novo Curso', 'foco-digital' ),
        'edit_item'          => __( 'Editar Curso', 'foco-digital' ),
        'view_item'          => __( 'Ver Curso', 'foco-digital' ),
        'all_items'          => __( 'Todos os Cursos', 'foco-digital' ),
        'search_items'       => __( 'Buscar Cursos', 'foco-digital' ),
        'parent_item_colon'  => __( 'Cursos Pai:', 'foco-digital' ),
        'not_found'          => __( 'Nenhum curso encontrado.', 'foco-digital' ),
        'not_found_in_trash' => __( 'Nenhum curso encontrado na Lixeira.', 'foco-digital' )
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => array( 'slug' => 'cursos', 'with_front' => false ),
        'capability_type'    => 'post',
        'has_archive'        => true,
        'hierarchical'       => false,
        'menu_position'      => 5,
        'menu_icon'          => 'dashicons-welcome-learn-more',
        'supports'           => array( 'title', 'editor', 'thumbnail', 'excerpt', 'revisions' ),
        'show_in_rest'       => true, // Suporte ao editor de blocos (Gutenberg)
    );

    register_post_type( 'cursos', $args );
}

// 2. ENQUEUE DO ARQUIVO CSS DA TABELA DE TURMAS
add_action( 'wp_enqueue_scripts', 'foco_enqueue_table_assets' );
function foco_enqueue_table_assets() {
    // Registra e enfileira o CSS do plugin
    wp_register_style( 'foco-tabela-turmas-style', plugin_dir_url( __FILE__ ) . 'tabela-turmas.css', array(), '1.0.0' );
    
    // Opcional: Só carrega se estiver na página de curso ou se o shortcode for detectado
    if ( is_singular( 'cursos' ) || is_page() ) {
        wp_enqueue_style( 'foco-tabela-turmas-style' );
    }
}

// 3. DEFINIÇÃO PROGRAMÁTICA DOS CAMPOS PERSONALIZADOS DO ACF (Advanced Custom Fields)
add_action( 'acf/init', 'foco_register_acf_fields' );
function foco_register_acf_fields() {
    if ( function_exists( 'acf_add_local_field_group' ) ):
        acf_add_local_field_group( array(
            'key' => 'group_foco_curso_data',
            'title' => 'Dados do Curso & Turmas',
            'fields' => array(
                array(
                    'key' => 'field_foco_duracao',
                    'label' => 'Duração do Curso (Texto)',
                    'name' => 'duracao_total',
                    'type' => 'text',
                    'instructions' => 'Carga horária total que aparece no topo da página. Ex: 63 horas',
                    'placeholder' => 'Ex: 63 horas',
                    'wrapper' => array( 'width' => '50' ),
                ),
                array(
                    'key' => 'field_foco_certificado',
                    'label' => 'Detalhes do Certificado',
                    'name' => 'detalhes_certificado',
                    'type' => 'text',
                    'instructions' => 'Selo explicativo do certificado. Ex: Certificado reconhecido',
                    'default_value' => 'Certificado de Conclusão reconhecido',
                    'wrapper' => array( 'width' => '50' ),
                ),
                array(
                    'key' => 'field_foco_turmas',
                    'label' => 'Cadastro de Turmas (Classes)',
                    'name' => 'turmas',
                    'type' => 'repeater',
                    'instructions' => 'Cadastre aqui as turmas disponíveis para este curso. Elas serão ordenadas e exibidas de forma automática.',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array( 'width' => '100' ),
                    'collapsed' => 'field_turma_datas_texto',
                    'min' => 0,
                    'max' => 0,
                    'layout' => 'block',
                    'button_label' => 'Adicionar Nova Turma',
                    'sub_fields' => array(
                        array(
                            'key' => 'field_turma_status',
                            'label' => 'Status da Turma',
                            'name' => 'status',
                            'type' => 'select',
                            'choices' => array(
                                'aberta'   => 'Inscrições Abertas',
                                'ultimas'  => 'Últimas Vagas',
                                'esgotada' => 'Esgotada',
                            ),
                            'default_value' => 'aberta',
                            'wrapper' => array( 'width' => '20' ),
                        ),
                        array(
                            'key' => 'field_turma_data_inicio',
                            'label' => 'Data de Início das Aulas',
                            'name' => 'data_inicio',
                            'type' => 'date_picker',
                            'instructions' => 'Usada para controle de expiração.',
                            'display_format' => 'd/m/Y',
                            'return_format' => 'Ymd',
                            'required' => 1,
                            'wrapper' => array( 'width' => '20' ),
                        ),
                        array(
                            'key' => 'field_turma_data_fim',
                            'label' => 'Data de Término (Opcional)',
                            'name' => 'data_fim',
                            'type' => 'date_picker',
                            'display_format' => 'd/m/Y',
                            'return_format' => 'Ymd',
                            'wrapper' => array( 'width' => '20' ),
                        ),
                        array(
                            'key' => 'field_turma_datas_texto',
                            'label' => 'Período por Extenso (Principal)',
                            'name' => 'datas_texto',
                            'type' => 'text',
                            'instructions' => 'Período que aparece em destaque.',
                            'placeholder' => 'Ex: 06 de julho a 16 de setembro',
                            'required' => 1,
                            'wrapper' => array( 'width' => '40' ),
                        ),
                        array(
                            'key' => 'field_turma_horario',
                            'label' => 'Horário das Aulas',
                            'name' => 'horario',
                            'type' => 'text',
                            'placeholder' => 'Ex: 19h às 22h',
                            'wrapper' => array( 'width' => '25' ),
                        ),
                        array(
                            'key' => 'field_turma_dias_semana',
                            'label' => 'Dias da Semana',
                            'name' => 'dias_semana',
                            'type' => 'text',
                            'placeholder' => 'Ex: Segundas e Quartas',
                            'wrapper' => array( 'width' => '25' ),
                        ),
                        array(
                            'key' => 'field_turma_local',
                            'label' => 'Local da Turma',
                            'name' => 'local',
                            'type' => 'text',
                            'placeholder' => 'Ex: Flamengo, Rio de Janeiro',
                            'wrapper' => array( 'width' => '50' ),
                        ),
                        array(
                            'key' => 'field_turma_preco_de',
                            'label' => 'Preço Original (Riscado)',
                            'name' => 'preco_de',
                            'type' => 'text',
                            'placeholder' => 'Ex: R$ 2.690,00',
                            'wrapper' => array( 'width' => '20' ),
                        ),
                        array(
                            'key' => 'field_turma_preco_por',
                            'label' => 'Preço com Desconto / Parcelamento',
                            'name' => 'preco_por',
                            'type' => 'text',
                            'placeholder' => 'Ex: 10x de R$ 214,90',
                            'required' => 1,
                            'wrapper' => array( 'width' => '40' ),
                        ),
                        array(
                            'key' => 'field_turma_preco_avista',
                            'label' => 'Preço à Vista',
                            'name' => 'preco_avista',
                            'type' => 'text',
                            'placeholder' => 'Ex: ou R$ 1.899,00 à vista',
                            'wrapper' => array( 'width' => '40' ),
                        ),
                        array(
                            'key' => 'field_turma_desconto_badge',
                            'label' => 'Badge de Desconto (Selinho)',
                            'name' => 'desconto_badge',
                            'type' => 'text',
                            'placeholder' => 'Ex: 30% OFF',
                            'wrapper' => array( 'width' => '20' ),
                        ),
                        array(
                            'key' => 'field_turma_datas_detalhadas',
                            'label' => 'Lista Completa de Datas (Hover / Descrição)',
                            'name' => 'datas_detalhadas',
                            'type' => 'textarea',
                            'rows' => 3,
                            'placeholder' => 'Ex: Datas das aulas: 6, 8, 13, 15, 20, 22 de julho...',
                            'wrapper' => array( 'width' => '100' ),
                        ),
                        array(
                            'key' => 'field_turma_checkout',
                            'label' => 'Link de Checkout para Matrícula',
                            'name' => 'link_checkout',
                            'type' => 'url',
                            'instructions' => 'Link do carrinho de pagamento da turma.',
                            'required' => 1,
                            'wrapper' => array( 'width' => '100' ),
                        ),
                    ),
                ),
            ),
            'location' => array(
                array(
                    array(
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'cursos',
                    ),
                ),
                array(
                    array(
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'page',
                    ),
                ),
            ),
            'menu_order' => 0,
            'position' => 'normal',
            'style' => 'default',
            'label_placement' => 'top',
            'instruction_placement' => 'label',
            'hide_on_screen' => '',
            'active' => true,
            'description' => 'Dados de turmas dinâmicas controlados pelo administrador do site.',
        ) );
    endif;
}

// 4. RENDERIZAÇÃO DO SHORTCODE [tabela_turmas]
add_shortcode( 'tabela_turmas', 'foco_render_tabela_turmas' );
function foco_render_tabela_turmas( $atts ) {
    // Permite buscar turmas de outro curso passando o ID: [tabela_turmas id="123"]
    $args = shortcode_atts( array(
        'id' => get_the_ID(),
        'hide_expired' => 'yes' // Oculta turmas que já iniciaram
    ), $atts );

    $post_id = intval( $args['id'] );
    
    // Verifica se o ACF está ativo e se o post tem turmas cadastradas
    if ( ! function_exists( 'get_field' ) ) {
        return '<p style="color:red; font-weight:bold;">O plugin Advanced Custom Fields (ACF) precisa estar ativo para exibir a tabela de turmas.</p>';
    }

    $turmas = get_field( 'turmas', $post_id );

    if ( empty( $turmas ) || ! is_array( $turmas ) ) {
        return '<div class="foco-no-classes">Não há turmas com inscrições abertas no momento. Entre em contato para saber sobre as próximas turmas.</div>';
    }

    // Filtra e ordena as turmas por data de início
    $hoje = date( 'Ymd' );
    $turmas_validas = array();

    foreach ( $turmas as $turma ) {
        $data_inicio = isset( $turma['data_inicio'] ) ? $turma['data_inicio'] : '';
        
        // Se configurado para esconder turmas iniciadas e a data de início for menor que hoje, pula
        if ( $args['hide_expired'] === 'yes' && ! empty( $data_inicio ) && $data_inicio < $hoje ) {
            continue;
        }
        
        $turmas_validas[] = $turma;
    }

    // Ordenação ascendente por data de início
    usort( $turmas_validas, function( $a, $b ) {
        return strcmp( $a['data_inicio'], $b['data_inicio'] );
    } );

    if ( empty( $turmas_validas ) ) {
        return '<div class="foco-no-classes">Não há turmas com inscrições abertas no momento. Entre em contato para saber sobre as próximas turmas.</div>';
    }

    // Enfileira o estilo caso não tenha sido feito ainda
    wp_enqueue_style( 'foco-tabela-turmas-style' );

    // Inicia buffering para gerar a saída HTML
    ob_start();
    ?>

    <div class="foco-agenda-wrapper">
        
        <!-- DESKTOP TABLE VIEW -->
        <div class="foco-table-desktop-container">
            <table class="foco-table-turmas">
                <thead>
                    <tr>
                        <th class="col-datas">Datas</th>
                        <th class="col-horario">Horário</th>
                        <th class="col-dias">Dias da Semana</th>
                        <th class="col-local">Local</th>
                        <th class="col-valores">Investimento</th>
                        <th class="col-botao"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ( $turmas_validas as $index => $turma ) : 
                        $status_class = 'status-' . esc_attr( $turma['status'] );
                        $status_label = 'Inscrições Abertas';
                        if ( $turma['status'] === 'ultimas' ) {
                            $status_label = 'Últimas Vagas';
                        } elseif ( $turma['status'] === 'esgotada' ) {
                            $status_label = 'Esgotada';
                        }
                    ?>
                        <tr class="<?php echo ($turma['status'] === 'esgotada') ? 'turma-esgotada' : ''; ?>">
                            <!-- Datas -->
                            <td class="cell-datas">
                                <span class="badge-status <?php echo $status_class; ?>"><?php echo esc_html( $status_label ); ?></span>
                                <div class="datas-periodo"><?php echo esc_html( $turma['datas_texto'] ); ?></div>
                                <?php if ( ! empty( $turma['datas_detalhadas'] ) ) : ?>
                                    <div class="datas-detalhe-tooltip" title="<?php echo esc_attr( $turma['datas_detalhadas'] ); ?>">
                                        <span class="dashicons dashicons-editor-help"></span> Ver calendário completo
                                    </div>
                                <?php endif; ?>
                            </td>
                            
                            <!-- Horário -->
                            <td class="cell-horario">
                                <strong><?php echo esc_html( $turma['horario'] ); ?></strong>
                            </td>
                            
                            <!-- Dias da semana -->
                            <td class="cell-dias">
                                <?php echo esc_html( $turma['dias_semana'] ); ?>
                            </td>
                            
                            <!-- Local -->
                            <td class="cell-local">
                                <span class="icon-map"><span class="dashicons dashicons-location"></span></span>
                                <span><?php echo esc_html( $turma['local'] ); ?></span>
                            </td>
                            
                            <!-- Valores -->
                            <td class="cell-valores">
                                <?php if ( ! empty( $turma['preco_de'] ) ) : ?>
                                    <div class="preco-riscado">De R$ <?php echo esc_html( $turma['preco_de'] ); ?> por</div>
                                <?php endif; ?>
                                <div class="preco-principal"><?php echo esc_html( $turma['preco_por'] ); ?></div>
                                <?php if ( ! empty( $turma['preco_avista'] ) ) : ?>
                                    <div class="preco- सविता"><?php echo esc_html( $turma['preco_avista'] ); ?></div>
                                <?php endif; ?>
                            </td>
                            
                            <!-- Botão de Compra -->
                            <td class="cell-botao">
                                <?php if ( $turma['status'] === 'esgotada' ) : ?>
                                    <button class="btn-checkout disabled" disabled>Esgotado</button>
                                <?php else : ?>
                                    <a href="<?php echo esc_url( $turma['link_checkout'] ); ?>" class="btn-checkout" target="_blank" rel="noopener">
                                        Comprar agora
                                        <?php if ( ! empty( $turma['desconto_badge'] ) ) : ?>
                                            <span class="badge-desconto"><?php echo esc_html( $turma['desconto_badge'] ); ?></span>
                                        <?php endif; ?>
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- MOBILE CARDS VIEW -->
        <div class="foco-cards-mobile-container">
            <?php foreach ( $turmas_validas as $turma ) : 
                $status_class = 'status-' . esc_attr( $turma['status'] );
                $status_label = 'Inscrições Abertas';
                if ( $turma['status'] === 'ultimas' ) {
                    $status_label = 'Últimas Vagas';
                } elseif ( $turma['status'] === 'esgotada' ) {
                    $status_label = 'Esgotada';
                }
            ?>
                <div class="foco-turma-card <?php echo ($turma['status'] === 'esgotada') ? 'turma-esgotada' : ''; ?>">
                    <!-- Cabeçalho do Card -->
                    <div class="card-header">
                        <span class="badge-status <?php echo $status_class; ?>"><?php echo esc_html( $status_label ); ?></span>
                        <?php if ( ! empty( $turma['desconto_badge'] ) && $turma['status'] !== 'esgotada' ) : ?>
                            <span class="badge-desconto-card"><?php echo esc_html( $turma['desconto_badge'] ); ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Datas principais -->
                    <div class="card-datas">
                        <div class="card-title">INSCRIÇÕES ABERTAS</div>
                        <div class="card-periodo"><?php echo esc_html( $turma['datas_texto'] ); ?></div>
                    </div>
                    
                    <!-- Informações Detalhadas -->
                    <div class="card-details-list">
                        <!-- Local -->
                        <div class="card-detail-item">
                            <span class="detail-icon"><span class="dashicons dashicons-location"></span></span>
                            <span class="detail-text">Aulas presenciais no <?php echo esc_html( $turma['local'] ); ?></span>
                        </div>
                        
                        <!-- Horário e Dias -->
                        <div class="card-detail-item">
                            <span class="detail-icon"><span class="dashicons dashicons-calendar-alt"></span></span>
                            <span class="detail-text"><?php echo esc_html( $turma['dias_semana'] ); ?> – Das <?php echo esc_html( $turma['horario'] ); ?></span>
                        </div>

                        <!-- Calendário Detalhado -->
                        <?php if ( ! empty( $turma['datas_detalhadas'] ) ) : ?>
                            <div class="card-detail-item data-detalhada">
                                <span class="detail-icon"><span class="dashicons dashicons-clock"></span></span>
                                <span class="detail-text"><strong>Datas das aulas:</strong> <?php echo esc_html( $turma['datas_detalhadas'] ); ?></span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Bloco de Preço -->
                    <div class="card-pricing">
                        <?php if ( ! empty( $turma['preco_de'] ) ) : ?>
                            <div class="card-price-de">TOTAL: de R$ <span class="risco"><?php echo esc_html( $turma['preco_de'] ); ?></span></div>
                        <?php endif; ?>
                        <div class="card-price-por">POR APENAS <?php echo esc_html( $turma['preco_por'] ); ?></div>
                        <?php if ( ! empty( $turma['preco_avista'] ) ) : ?>
                            <div class="card-price-avista"><?php echo esc_html( $turma['preco_avista'] ); ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- Botão de Ação -->
                    <div class="card-action">
                        <?php if ( $turma['status'] === 'esgotada' ) : ?>
                            <button class="btn-checkout-card disabled" disabled>Esgotada</button>
                        <?php else : ?>
                            <a href="<?php echo esc_url( $turma['link_checkout'] ); ?>" class="btn-checkout-card" target="_blank" rel="noopener">
                                Inscreva-se Agora
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

    </div>

    <?php
    return ob_get_clean();
}
