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

                            Responda **somente** com um JSON, no seguinte formato:

                            {
                            "rg": true ou false,
                            "cin": true ou false,
                            "porcentagem": "Porcentagem de semelhança entre os documentos (ex: 85.3)",
                            "motivo": "Explicação curta e objetiva da decisão",
                            "tipo_documento": "Tipo de documento identificado na segunda imagem",
                            "documento_aberto": true ou false
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

function consultarApi(array $data)
{
    try {
        $headers = array(
            "Content-Type: application/json", 
            sprintf("Authorization: Bearer %s", API_KEY)
        );

        # Inicia o cURL.
        $ch = curl_init(API_ENDPOINT);

        # Configura cURL.
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);

        error_log($response);

        # Exibe a resposta de acordo com o status (com erro ou sem).
        if (curl_errno($ch))
        {
            echo sprintf("Erro: %s", curl_error($ch));
            
            $retorno = [
                "error" => true,
                "data" => "",
                "msg" => ""
            ];

            return $retorno;
        }

        $data = json_decode($response, true);
        $dataArray = json_decode($data["choices"][0]["message"]["content"], true);

        $retorno = [
            "error" => false,
            "data" => $data["choices"][0]["message"]["content"],
            "msg" => null
        ];

    } catch (Throwable $e)
    {
        $retorno = [
            "error" => true,
            "data" => $e->getMessage(),
            "msg" => $e->getMessage()
        ];
    } finally
    {
        curl_close($ch);

        return $retorno;
    }
}

# Habilita logs.
ini_set("log_errors", true);
ini_set("error_log", ARQUIVO_LOG);

# Move o arquivo para o diretório especificado
$destino = sprintf("%s%s", DIR_ARQUIVO, basename($_FILES["file"]["name"]));

try {
    if (move_uploaded_file($_FILES["file"]["tmp_name"], $destino))
    {
        // Resgata o arquivo movido para enviar para a API
        $data = definirDados($destino);
        $response = consultarApi($data);

        echo json_encode($response);
    } else
    {
        echo json_encode([
            "error" => true,
            "msg" => "Falha ao mover o arquivo.",
            'data' => 'Falha ao mover o arquivo'
        ]);
    }
} catch (Throwable $e)
{
    echo json_encode([
        "error" => true,
        "msg" => $e->getMessage(),
        'data' => $e->getMessage()
    ]);
}
