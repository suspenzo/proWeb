<?php

function enviarCorreoVerificacion($emailDestino, $token) {

    // 👉 Reemplaza con tu nueva API key
    $apiKey = "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";

    // 👉 Correo remitente (Gmail), debe estar verificado en Brevo
    $correoRemitente = "ayalaenzoc@gmail.com";

    // 👉 Enlace de verificación
    $urlVerificacion = "https://enzo.x10.network/verificar.php?token=" . urlencode($token);

    // Contenido del correo
    $html = "
        <h2>Verificación de cuenta</h2>
        <p>Haz clic en el siguiente botón para verificar tu correo:</p>
        <a href='$urlVerificacion' 
           style='padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px;'>
           Verificar cuenta
        </a>
        <p>Si no puedes hacer clic, copia este enlace en tu navegador:</p>
        <p>$urlVerificacion</p>
    ";

    // Datos para la API
    $data = [
        "sender" => [
            "name" => "Mi Web",
            "email" => $correoRemitente
        ],
        "to" => [
            ["email" => $emailDestino]
        ],
        "subject" => "Verificación de cuenta",
        "htmlContent" => $html
    ];

    // Configurar cURL
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => "https://api.brevo.com/v3/smtp/email",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            "accept: application/json",
            "api-key: $apiKey",
            "content-type: application/json",
        ],
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($data)
    ]);

    // Ejecutar
    $response = curl_exec($curl);
    $error = curl_error($curl);
    curl_close($curl);

    // Manejo de errores
    if ($error) {
        return "Error cURL: " . $error;
    }

    return $response; // Brevo devolverá messageId si todo fue bien
}
