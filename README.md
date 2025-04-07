# Plugin Turmas de Pilates

Este plugin permite gerenciar turmas de cursos de pilates, organizando-as por estado e cidade, com informações detalhadas sobre cada turma.

## Funcionalidades

- Criação de turmas organizadas por estado e cidade
- Gerenciamento de múltiplas turmas por cidade
- Informações sobre módulos e status de vagas
- Filtro de busca por estado e cidade no frontend
- Interface responsiva e amigável
- Todos os estados brasileiros são cadastrados automaticamente

## Instalação

1. Faça o upload da pasta `turmas-pilates` para o diretório `/wp-content/plugins/`
2. Ative o plugin através do menu 'Plugins' no WordPress
3. Os estados brasileiros são cadastrados automaticamente durante a ativação do plugin
4. Agora basta criar as turmas com as respectivas cidades associadas a cada estado

## Uso

### No Painel Administrativo

1. Acesse o menu "Turmas" no painel administrativo
2. Clique em "Adicionar Nova" para criar uma nova turma
3. Preencha o título com o nome da cidade
4. Selecione o estado correspondente na lista de estados pré-cadastrados
5. No editor, adicione informações adicionais sobre a turma
6. Na caixa "Informações da Turma", adicione quantas turmas forem necessárias, preenchendo:
   - Número da turma
   - Datas do Módulo I
   - Datas do Módulo II
   - Status das vagas

### No Frontend

Para exibir o filtro de turmas em qualquer página ou post, utilize o shortcode:

```
[turmas_pilates]
```

O shortcode irá exibir:
1. Um seletor de estados (somente estados que possuem turmas cadastradas)
2. Um seletor de cidades (que será preenchido automaticamente com base no estado selecionado)
3. As turmas disponíveis para a cidade selecionada

## Estrutura do Plugin

- `turmas-pilates.php` - Arquivo principal do plugin
- `shortcode.php` - Implementação do shortcode e funções AJAX
- `css/admin.css` - Estilos para o painel administrativo
- `css/frontend.css` - Estilos para o frontend
- `js/frontend.js` - Scripts para o frontend

## Requisitos

- WordPress 5.0 ou superior
- PHP 7.0 ou superior 