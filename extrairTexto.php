<?php

define("API_KEY", '');
define("API_ENDPOINT", "https://api.openai.com/v1/chat/completions");
define("API_MODEL", "gpt-4o");

define("MAXIMO_TOKEN", 2048);
define("ARQUIVO_LOG", "D:/Dudu/dev/Apache24/htdocs/php-openai-ocr/log.txt");
define("DIR_ARQUIVO", "D:/Dudu/dev/Apache24/htdocs/php-openai-ocr/files/");

function definirDados($arquivo)
{
    $templateRg = base64_encode(file_get_contents('D:/Dudu/dev/Apache24/htdocs/php-openai-ocr/files/template-rg.jpg'));
    $rgBase64 = 'data:image/jpeg;base64,' . $templateRg;

    $templateCIN = base64_encode(file_get_contents('D:/Dudu/dev/Apache24/htdocs/php-openai-ocr/files/template-cin.jpeg'));
    $cinBase64 = 'data:image/jpeg;base64,' . $templateCIN;

    // Lê o conteúdo da imagem e converte para base64
    $imageData = base64_encode(file_get_contents($arquivo));
    $imageBase64 = sprintf("data:image/jpeg;base64,%s", $imageData); // Ajuste o MIME type conforme necessário



    return [
        "model" => API_MODEL,
        "messages" => [
            [
                "role" => "user",
                "content" => [
                    [
                        "type" => "text",
                        "text" => '
                            Compare as duas imagens de documentos de identidade fornecidas:
                            - A **primeira imagem** é um modelo de referência de um RG.
                            - A **segunda imagem** é um modelo de referência de um CIN.
                            - A **terceira imagem** foi enviada por um usuário.

                            Avalie se a terceira imagem é **semelhante ao modelo de RG** ou **semelhante ao modelo de CIN**, considerando os seguintes critérios:

                            - Estrutura visual (layout, campos presentes)
                            - Posição e organização dos dados
                            - Aparência geral (se parece com um RG legítimo ou outro tipo de documento)
                            - O documento deve estar aberto igual ao modelo
                            - Caso haja dúvidas, verifique a terceira imagem e diga qual modelo ela se assemelha mais, mesmo que a semelhança seja baixa.
                            
                            Campos
                            - RG: Se o documento da terceira imagem for semelhante ao modelo de RG, retorne `true`. Caso contrário, retorne `false`.
                            - CIN: Se o documento da terceira imagem for semelhante ao modelo de CIN, retorne `true`. Caso contrário, retorne `false`.
                            - Porcentagem de semelhança: Retorne a porcentagem de semelhança entre a terceira imagem e o modelo mais próximo (RG ou CIN).
                            - Motivo: Forneça uma explicação curta e objetiva da decisão, destacando os principais pontos de comparação.
                            - Documento aberto: Indique se o documento da terceira imagem está aberto de forma semelhante ao modelo (ou seja, se os campos estão visíveis e organizados de maneira similar).
                            - Válido: Indique se o documento da terceira imagem é considerado válido com base na semelhança e na estrutura. Documentos são considerados válidos se estiverem no período de 10 anos a partir da data de emissão, ou seja, se a data de emissão for inferior a 10 anos a partir da data atual.
                            Responda **somente** com um JSON, no seguinte formato:

                            {
                                "rg": true ou false,
                                "cin": true ou false,
                                "porcentagem": "Porcentagem de semelhança entre os documentos (ex: 85.3)",
                                "motivo": "Explicação curta e objetiva da decisão",
                                "documento_aberto": true ou false,
                                "validade": Válido até XX/XX/XXXX ou Vencido em XX/XX/XXXX
                            }

                            **Importante**: não inclua explicações fora do JSON e não use formatação adicional como Markdown.

                        '
                    ],
                    [
                        "type" => "image_url",
                        "image_url" => ["url" => $rgBase64]
                    ],
                    [
                        "type" => "image_url",
                        "image_url" => ["url" => $cinBase64]
                    ],
                    [
                        "type" => "image_url",
                        "image_url" => ["url" => $imageBase64]
                    ]
                ]
            ]
        ],
        "max_tokens" => MAXIMO_TOKEN
    ];


}

function requisitarCUrl(string $url, array $cabeçalhos, array $dados) 
{
    if (empty($cabeçalhos) || empty($dados) || empty($url)) {
        throw new InvalidArgumentException("Os cabeçalhos, os dados e a URL não podem ser vazios.");
    }

    $ch = curl_init($url);

    # Configura cURL.
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($dados));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $cabeçalhos);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $resposta = curl_exec($ch);

    error_log($resposta);

    $houveErro = curl_errno($ch);

    if ($houveErro) {
        $erro = curl_error($ch);
        throw new RuntimeException("Erro na requisição cURL: $erro");
    }

    return  json_decode($resposta, true);

}

function realizarUploadArquivo()
{
    $nomeDoArquivo    = $_FILES["file"]["name"];
    $diretorioDestino = DIR_ARQUIVO;
    $destino          = "$diretorioDestino$nomeDoArquivo";

    $arquivoTemporario = $_FILES["file"]["tmp_name"];

    if (!move_uploaded_file($arquivoTemporario, $destino)) {
        throw new RuntimeException("Falha ao mover o arquivo para o diretório de destino.");
    }

    $mimesPermitidos = ['image/jpeg', 'image/png', 'image/jpg'];
    
    if (!in_array(mime_content_type($destino), $mimesPermitidos)) {
        unlink($destino);
        throw new InvalidArgumentException("Tipo de arquivo não permitido. Apenas JPEG e PNG são aceitos.");
    }
    
    return $destino;
}

function extrairInformacoesViaOpenAI()
{
    $destino = realizarUploadArquivo();
    $chaveAPI = API_KEY;

    $cabecalhos = ['Content-Type: application/json', "Authorization: Bearer $chaveAPI"];


    $dados     = definirDados($destino);
    $resposta = requisitarCUrl(API_ENDPOINT, $cabecalhos, $dados);

    return $resposta;
}


try {
    $openAI = extrairInformacoesViaOpenAI();

    echo json_encode([
        "error" => false,
        "msg"   => "Arquivo processado com sucesso.",
        'data'  => json_decode($openAI['choices'][0]['message']['content'], true)
    ]);
    
} catch (InvalidArgumentException $iae) {
    echo json_encode([
        "error" => true,
        "msg"   => $iae->getMessage(),
        'data'  => $iae->getMessage()
    ]);
} catch (Exception $e) {
    echo json_encode([
        "error" => true,
        "msg"   => "Ocorreu um erro ao processar o arquivo.",
        'data' => $e->getMessage()
    ]);
}
