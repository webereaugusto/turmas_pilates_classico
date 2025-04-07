jQuery(document).ready(function($) {
    // Carregar dashicons
    $('head').append('<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/dashicons/0.9.0/css/dashicons.min.css">');
    
    // Configuração para o datepicker brasileiro
    $.datepicker.regional['pt-BR'] = {
        closeText: 'Fechar',
        prevText: '&#x3C;Anterior',
        nextText: 'Próximo&#x3E;',
        currentText: 'Hoje',
        monthNames: ['Janeiro','Fevereiro','Março','Abril','Maio','Junho',
        'Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'],
        monthNamesShort: ['Jan','Fev','Mar','Abr','Mai','Jun',
        'Jul','Ago','Set','Out','Nov','Dez'],
        dayNames: ['Domingo','Segunda-feira','Terça-feira','Quarta-feira','Quinta-feira','Sexta-feira','Sábado'],
        dayNamesShort: ['Dom','Seg','Ter','Qua','Qui','Sex','Sáb'],
        dayNamesMin: ['Dom','Seg','Ter','Qua','Qui','Sex','Sáb'],
        weekHeader: 'Sm',
        dateFormat: 'dd/mm/yy',
        firstDay: 0,
        isRTL: false,
        showMonthAfterYear: false,
        yearSuffix: ''
    };
    $.datepicker.setDefaults($.datepicker.regional['pt-BR']);
    
    // Função para inicializar datepickers
    function initDatepickers() {
        // Para todos os campos de data
        $('.single-date-field').each(function() {
            if (!$(this).hasClass('hasDatepicker')) {
                initSingleDatepicker($(this));
            }
        });
    }
    
    // Função para inicializar um datepicker simples
    function initSingleDatepicker(element) {
        // Configurar o datepicker
        element.datepicker({
            dateFormat: 'dd/mm/yy',
            changeMonth: true,
            changeYear: true,
            showOtherMonths: true,
            selectOtherMonths: true
        });
        
        // Adicionar evento para clicar no ícone do calendário
        element.siblings('.date-icon').on('click', function() {
            element.datepicker('show');
        });
    }
    
    // Inicializar datepickers existentes
    initDatepickers();
    
    // Observar quando novos campos são adicionados
    $(document).on('click', '#add-turma, #add-turma-meio-semana', function() {
        // Esperar um momento para que os novos campos sejam renderizados
        setTimeout(function() {
            // Refazer datepickers para os novos campos
            initDatepickers();
        }, 100);
    });
}); 