<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js" integrity="sha384-k6d4wzSIapyDyv1kpU366/PK5hCdSbCRGRCMv+eplOQJWyd1fbcAu9OCUj5zNLiq" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
</head>
<body class="bg-body-secondary">
    <div class="container">
        <h1 style="text-align: center;" class="mb-5">Extrair texto de uma imagem</h1>
        <div class="d-flex justify-content-center align-items-center mb-3">
            <form action="extrairTexto.php" method="post">
                <div class="mb-3">
                    <input class="form-control form-control-lg shadow" type="file" name="file" style="display: inline;">
                </div>

                <div>
                    <button class="btn btn-primary">Enviar</button>
                </div>
            </form>
        </div>

        <div class="spinner-grow text-secondary mb-3" role="status" id="loadDiv" style="display: none;"></div>

        <pre id="result"></pre>
    </div>
    <script>
        $("form").on("submit", function(evento)
        {
            evento.preventDefault();

            const data = new FormData(this);

            $.ajax({
                url: $(this).attr("action"),
                type: $(this).attr("method"),
                data: data,
                dataType: "json",
                cache: false,
                contentType: false,
                processData: false,
                beforeSend: function()
                {
                    $(this).prop("disabled", true);
                    $("#loadDiv").css("display", "block");
                },
                success: function(response)
                {
                    $('#result').html(response['data']);
                },
                complete: function()
                {
                    $(this).prop("disabled", false);
                    $("#loadDiv").css("display", "none");
                },
            });
        });

        $("input").on("change", function()
        {
            $("form").submit();
        });

        
    </script>
</body>
</html>