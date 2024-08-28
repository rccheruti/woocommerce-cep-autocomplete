<?php
/*
Plugin Name: WooCommerce CEP Autocomplete
Description: Preenche automaticamente o endereço no checkout do WooCommerce com base no CEP e formata o campo de CEP.
Version: 1.1
Author: Roger Cheruti
Author URI: https://rcheruti.com
*/

// Impede o acesso direto ao arquivo
if (!defined('ABSPATH')) {
    exit;
}

// Adiciona o script para buscar o CEP e a máscara no checkout do WooCommerce
function wca_enqueue_scripts() {
    // Certifique-se de que o jQuery está carregado
    wp_enqueue_script('jquery');

    // Adiciona a biblioteca jQuery Mask Plugin via CDN
    wp_enqueue_script('jquery-mask', 'https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js', array('jquery'), '1.14.16', true);

    // Adiciona o script customizado para autocomplete e formatação de CEP
    wp_enqueue_script('wca-cep-autocomplete', plugin_dir_url(__FILE__) . 'js/cep-autocomplete.js', array('jquery', 'jquery-mask'), '1.0', true);
}
add_action('wp_enqueue_scripts', 'wca_enqueue_scripts');

// Adiciona o código necessário no footer para garantir que o jQuery e o script sejam carregados
function wca_add_custom_js() {
    if (is_checkout()) {
        ?>
        <script type="text/javascript">
            jQuery(document).ready(function($) {
                // Aplica a máscara de CEP
                $('#billing_postcode').mask('00000-000');

                // Monitora o campo de CEP e busca o endereço
                $('#billing_postcode').on('blur', function() {
                    var cep = $(this).val().replace(/\D/g, ''); // Remove qualquer caractere não numérico

                    if (cep.length === 8) { // Verifica se o CEP tem 8 dígitos
                        $.getJSON('https://viacep.com.br/ws/' + cep + '/json/', function(dados) {
                            if (!("erro" in dados)) {
                                // Preenche os campos com os valores retornados pela API
                                $('#billing_address_1').val(dados.logradouro);
                                $('#billing_neighborhood').val(dados.bairro);
                                $('#billing_city').val(dados.localidade);
                                $('#billing_state').val(dados.uf);
                            } else {
                                alert('CEP não encontrado.');
                            }
                        });
                    } else {
                        alert('Por favor, insira um CEP válido.');
                    }
                });
            });
        </script>
        <?php
    }
}
add_action('wp_footer', 'wca_add_custom_js', 20);
