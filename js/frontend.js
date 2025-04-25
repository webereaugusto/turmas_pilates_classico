jQuery(document).ready(function($) {
    // Variáveis de controle de retentativa
    var maxRetries = 3;
    var retryDelay = 1000; // 1 segundo

    // Função para carregar cidades baseado no estado selecionado
    function carregarCidades(estadoId, tentativa = 1) {
        console.log('Carregando cidades para estadoId:', estadoId, 'Tentativa:', tentativa);
        
        return $.ajax({
            url: turmasPilates.ajaxurl,
            type: 'POST',
            data: {
                action: 'turmas_pilates_get_cidades',
                estado_id: estadoId,
                nonce: turmasPilates.nonce
            },
            success: function(response) {
                console.log('Resposta AJAX:', response);
                if (response.success) {
                    var $cidadeSelect = $('#turmas-cidade');
                    $cidadeSelect.empty();
                    $cidadeSelect.append('<option value="">Selecione a Cidade</option>');
                    
                    $.each(response.data, function(id, nome) {
                        $cidadeSelect.append($('<option></option>').val(id).text(nome));
                    });
                } else {
                    console.error('Falha na resposta:', response.data);
                    if (tentativa < maxRetries) {
                        setTimeout(function() {
                            carregarCidades(estadoId, tentativa + 1);
                        }, retryDelay);
                    }
                }
            },
            error: function(xhr, status, error) {
                console.error('Erro AJAX:', status, error, xhr.responseText);
                if (tentativa < maxRetries) {
                    setTimeout(function() {
                        carregarCidades(estadoId, tentativa + 1);
                    }, retryDelay);
                }
            }
        });
    }

    // Cache do último estado selecionado
    var ultimoEstadoSelecionado = '';

    // Evento de mudança no select de estado
    $('#turmas-estado').on('change', function() {
        var estadoId = $(this).val();
        if (estadoId && estadoId !== ultimoEstadoSelecionado) {
            ultimoEstadoSelecionado = estadoId;
            carregarCidades(estadoId);
        } else if (!estadoId) {
            $('#turmas-cidade').empty().append('<option value="">Selecione a Cidade</option>');
            ultimoEstadoSelecionado = '';
        }
    });

    // Evento de mudança no select de cidade
    $('#turmas-cidade').on('change', function() {
        var cidadeId = $(this).val();
        if (cidadeId) {
            $.ajax({
                url: turmasPilates.ajaxurl,
                type: 'POST',
                data: {
                    action: 'turmas_pilates_get_turmas',
                    cidade_id: cidadeId,
                    nonce: turmasPilates.nonce
                },
                success: function(response) {
                    if (response.success) {
                        var $container = $('#turmas-resultado');
                        $container.empty();
                        
                        var turmas_final_semana = response.data.final_semana || [];
                        var turmas_meio_semana = response.data.meio_semana || [];
                        var info_adicional = response.data.info_adicional || {};
                        
                        // Forçar cast para array em caso de objeto
                        if (turmas_final_semana && typeof turmas_final_semana === 'object' && !Array.isArray(turmas_final_semana)) {
                            var temp = [];
                            $.each(turmas_final_semana, function(key, value) {
                                temp.push(value);
                            });
                            turmas_final_semana = temp;
                        }
                        
                        if (turmas_meio_semana && typeof turmas_meio_semana === 'object' && !Array.isArray(turmas_meio_semana)) {
                            var temp = [];
                            $.each(turmas_meio_semana, function(key, value) {
                                temp.push(value);
                            });
                            turmas_meio_semana = temp;
                        }
                        
                        var temTurmas = (turmas_final_semana && turmas_final_semana.length > 0) || 
                                       (turmas_meio_semana && turmas_meio_semana.length > 0);
                        
                        if (temTurmas) {
                            // Obter o nome da cidade e estado
                            var cidadeNome = $('#turmas-cidade option:selected').text();
                            var estadoNome = $('#turmas-estado option:selected').text();
                            
                            // Exibir turmas de final de semana
                            if (turmas_final_semana.length > 0) {
                                $container.append(`<h2 class="turmas-categoria">Próximas Turmas em ${cidadeNome} - ${estadoNome}</h2>`);
                                var $turmasFdsContainer = $('<div class="turmas-categoria-container"></div>');
                                
                                $.each(turmas_final_semana, function(index, turma) {
                                var statusClass = turma.status === 'abertas' ? 'status-abertas' : 
                                                turma.status === 'esgotadas' ? 'status-esgotadas' : 'status-restantes';
                                                
                                var statusText = turma.status === 'abertas' ? 'Vagas Abertas' : 
                                                   turma.status === 'esgotadas' ? 'Vagas Esgotadas' : 
                                                   (turma.qtd_vagas ? `Vagas Restantes: ${turma.qtd_vagas}` : 'Vagas Restantes');
                                    
                                    // Constrói a exibição de quantidade de vagas restantes
                                    var qtdVagas = '';
                                    if (turma.status === 'restantes' && turma.qtd_vagas) {
                                        qtdVagas = `<p class="vagas-restantes-contador">RESTAM POUCAS VAGAS!</p>`;
                                    }
                                    
                                    // Formatar datas dos módulos
                                    var moduloText = '';
                                    if (turma.modulo_data1 || turma.modulo_data2 || turma.modulo_data3) {
                                        var datas = [];
                                        if (turma.modulo_data1) datas.push(turma.modulo_data1);
                                        if (turma.modulo_data2) datas.push(turma.modulo_data2);
                                        if (turma.modulo_data3) datas.push(turma.modulo_data3);
                                        moduloText = datas.join(' e ');
                                    }
                                    
                                    var html = `
                                        <div class="turma-card turma-fds">
                                            <h3>Turma ${turma.numero}</h3>
                                            <p><strong>Datas desta turma:</strong> ${moduloText}</p>
                                            ${qtdVagas}
                                            <span class="status-badge ${statusClass}">${statusText}</span>
                                        </div>
                                    `;
                                    $turmasFdsContainer.append(html);
                                });
                                
                                // Adicionar informações de local e contato para turmas de final de semana
                                if (info_adicional.info_fds) {
                                    // Limpar e escapar o conteúdo
                                    var infoText = info_adicional.info_fds || '';
                                    // Substituir quebras de linha por <br>
                                    infoText = infoText.replace(/\n/g, '<br>');
                                    
                                    var infoHtml = `
                                        <div class="turma-location-contact">
                                            <h3>Informações sobre o local do curso</h3>
                                            <div>${infoText}</div>
                                        </div>
                                    `;
                                    $turmasFdsContainer.append(infoHtml);
                                }
                                
                                $container.append($turmasFdsContainer);
                            }
                            
                            // Exibir turmas durante a semana
                            if (turmas_meio_semana.length > 0) {
                                $container.append(`<h2 class="turmas-categoria">Turmas Durante a Semana em ${cidadeNome} - ${estadoNome}</h2>`);
                                var $turmasSemanaContainer = $('<div class="turmas-categoria-container"></div>');
                                
                                $.each(turmas_meio_semana, function(index, turma) {
                                    var statusClass = turma.status === 'abertas' ? 'status-abertas' : 
                                                    turma.status === 'esgotadas' ? 'status-esgotadas' : 'status-restantes';
                                                    
                                    var statusText = turma.status === 'abertas' ? 'Vagas Abertas' : 
                                                   turma.status === 'esgotadas' ? 'Vagas Esgotadas' : 
                                                   (turma.qtd_vagas ? `Vagas Restantes: ${turma.qtd_vagas}` : 'Vagas Restantes');
                                    
                                    // Constrói a exibição de quantidade de vagas restantes
                                    var qtdVagas = '';
                                    if (turma.status === 'restantes' && turma.qtd_vagas) {
                                        qtdVagas = `<p class="vagas-restantes-contador">RESTAM POUCAS VAGAS!</p>`;
                                    }
                                    
                                    // Formatar datas dos módulos
                                    var moduloText = '';
                                    if (turma.modulo_data1 || turma.modulo_data2 || turma.modulo_data3) {
                                        var datas = [];
                                        if (turma.modulo_data1) datas.push(turma.modulo_data1);
                                        if (turma.modulo_data2) datas.push(turma.modulo_data2);
                                        if (turma.modulo_data3) datas.push(turma.modulo_data3);
                                        moduloText = datas.join(' e ');
                                    }
                                
                                var html = `
                                        <div class="turma-card turma-semana">
                                        <h3>Turma ${turma.numero}</h3>
                                            <p><strong>Datas desta turma:</strong> ${moduloText}</p>
                                            ${qtdVagas}
                                        <span class="status-badge ${statusClass}">${statusText}</span>
                                    </div>
                                `;
                                    $turmasSemanaContainer.append(html);
                                });
                                
                                // Adicionar informações de local e contato para turmas durante a semana
                                if (info_adicional.info_meio_semana) {
                                    // Limpar e escapar o conteúdo
                                    var infoText = info_adicional.info_meio_semana || '';
                                    // Substituir quebras de linha por <br>
                                    infoText = infoText.replace(/\n/g, '<br>');
                                    
                                    var infoHtml = `
                                        <div class="turma-location-contact">
                                            <h3>Informações sobre o local do curso</h3>
                                            <div>${infoText}</div>
                                        </div>
                                    `;
                                    $turmasSemanaContainer.append(infoHtml);
                                }
                                
                                $container.append($turmasSemanaContainer);
                            }
                        } else {
                            $container.html('<p>Nenhuma turma encontrada para esta cidade.</p>');
                        }
                    }
                }
            });
        } else {
            $('#turmas-resultado').empty();
        }
    });
}); 