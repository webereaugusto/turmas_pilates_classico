jQuery(document).ready(function($) {
    // Configurações do datepicker
    function initDatepickers() {
        $('.single-date-field').datepicker({
            dateFormat: 'dd/mm/yy',
            dayNames: ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado'],
            dayNamesMin: ['D', 'S', 'T', 'Q', 'Q', 'S', 'S', 'D'],
            dayNamesShort: ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'],
            monthNames: ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'],
            monthNamesShort: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'],
            nextText: 'Próximo',
            prevText: 'Anterior',
            changeMonth: true,
            changeYear: true,
            yearRange: 'c-1:c+2'
        });
    }
    
    // Inicializar datepickers para campos já existentes
    initDatepickers();
    
    // Reiniciar datepickers quando novos campos são adicionados
    $(document).on('click', '.add-turma-button, #add-turma-meio-semana', function() {
        setTimeout(function() {
            initDatepickers();
        }, 100);
    });
}); 