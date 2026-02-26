# PHP OpenAI OCR

Sistema de reconhecimento óptico de caracteres (OCR) e validação de documentos de identidade brasileiros (RG e CIN) utilizando a API GPT-4 Vision da OpenAI.

## Descrição

Este projeto é uma aplicação web desenvolvida em PHP que permite o upload de imagens de documentos de identidade (RG ou CIN) e utiliza a inteligência artificial da OpenAI para:

- **Identificar o tipo de documento** (RG ou CIN)
- **Comparar com modelos de referência** para validação visual
- **Extrair informações** dos documentos
- **Validar validade** do documento (documentos válidos até 10 anos de emissão)
- **Verificar se o documento está aberto** de forma semelhante ao modelo de referência

## Funcionalidades

✅ Interface web intuitiva com Bootstrap 5  
✅ Upload de documentos em JPEG e PNG  
✅ Processamento de imagens via API GPT-4 Vision  
✅ Análise comparativa com templates de RG e CIN  
✅ Validação de documentos identificados  
✅ Resposta em JSON estruturado  
✅ Sistema de logging para rastreamento de requisições  

## Requisitos

- **PHP** 7.4 ou superior
- **cURL** habilitado no PHP
- Acesso à **API OpenAI** (chave de API válida)
- **Servidor web** Apache, Nginx ou similar

## Instalação

1. Clone ou faça download do projeto:
```bash
git clone <repository-url>
cd php-openai-ocr
```

2. Configure o diretório web (Apache, Nginx, etc.) para apontar para esta pasta

3. **Importante**: Crie um arquivo `log.txt` vazio na raiz do projeto:
```bash
touch log.txt
```

4. Crie o diretório `files/` se ainda não existir:
```bash
mkdir files
```

5. Adicione os templates dos documentos adicionando as imagens `template-rg.jpg` e `template-cin.jpeg` na pasta `files/`

## Configuração

No arquivo [extrairTexto.php](extrairTexto.php), localize e configure as constantes:

```php
define("API_KEY", "sua-chave-api-openai-aqui");
define("API_ENDPOINT", "https://api.openai.com/v1/chat/completions");
define("API_MODEL", "gpt-4o");
define("MAXIMO_TOKEN", 2048);
define("ARQUIVO_LOG", "caminho/para/seu/log.txt");
define("DIR_ARQUIVO", "caminho/para/diretorio/files/");
```

⚠️ **Segurança**: Proteja sua chave de API! Considere usar variáveis de ambiente em produção.

## Uso

1. Acesse [index.php](index.php) no navegador
2. Clique em "Selecionar arquivo" e escolha uma imagem de RG ou CIN
3. Clique em "Enviar"
4. Aguarde o processamento (ícone de carregamento será exibido)
5. Os resultados serão exibidos em formato JSON

### Resposta de Sucesso

```json
{
  "error": false,
  "msg": "Arquivo processado com sucesso.",
  "data": {
    "rg": true,
    "cin": false,
    "porcentagem": "92.5",
    "motivo": "Documentação compatível com padrão de RG brasileiro",
    "documento_aberto": true,
    "validade": "Válido até 15/03/2034"
  }
}
```

### Resposta de Erro

```json
{
  "error": true,
  "msg": "Tipo de arquivo não permitido. Apenas JPEG e PNG são aceitos.",
  "data": "Tipo de arquivo não permitido. Apenas JPEG e PNG são aceitos."
}
```

## Estrutura do Projeto

```
php-openai-ocr/
├── index.php              # Página principal com formulário
├── extrairTexto.php       # Backend que processa a requisição
├── README.md              # Este arquivo
├── log.txt                # Arquivo de log (criar manualmente)
├── files/                 # Diretório para upload de arquivos
│   ├── template-rg.jpg    # Template de RG para comparação
│   └── template-cin.jpeg  # Template de CIN para comparação
└── src/                   # Pasta reservada para expansões futuras
```

## Detalhes Técnicos

### Tipos de Arquivo Suportados
- JPEG (.jpg, .jpeg)
- PNG (.png)

### Fluxo de Processamento

1. **Upload** → Um arquivo é enviado via formulário POST
2. **Validação** → Verifica tipo MIME e move para diretório de destino
3. **Base64** → Codifica a imagem e os templates em base64
4. **API OpenAI** → Envia requisição com análise visual GPT-4
5. **Resposta** → Processa e retorna resultado em JSON

### Critérios de Validação

O documento é considerado válido quando:
- ✓ Corresponde ao tipo correto (RG ou CIN)
- ✓ Está aberto de forma legível
- ✓ Está dentro do período de validade (≤ 10 anos de emissão)
- ✓ Semelhança com o template ≥ certo limiar

## Troubleshooting

| Problema | Solução |
|----------|---------|
| "Tipo de arquivo não permitido" | Verifique se os templates estão no diretório `files/` |
| Erro de permissão | Certifique-se que o PHP tem permissão de escrita em `files/` |
| Chave de API inválida | Valide sua chave no painel da OpenAI |
| cURL não funcionando | Ative a extensão cURL no php.ini |

## Termos de Uso

Este projeto faz uso da API OpenAI. Consulte os [termos de serviço da OpenAI](https://openai.com/terms) para informações sobre limites de uso e custos.

## Licença

Este projeto é fornecido como está, sem garantias.

## Autor

Desenvolvido como ferramenta de validação de documentos via IA.

---

⚠️ **Nota Importante**: Mantenha suas credenciais de API seguras. Nunca publique commits com chaves de API expostas.
