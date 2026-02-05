<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Erro Interno do Servidor</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <style>
        :root {
            --primary-color: #6EC1E4;
            --secondary-color: #54595F;
            --text-color: #7A7A7A;
            --bg-color: #FFFFFF;
        }
        body {
            background-color: var(--bg-color);
            color: var(--text-color);
            font-family: 'Figtree', sans-serif;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            text-align: center;
        }
        .container {
            padding: 2rem;
        }
        .error-code {
            font-size: 6rem;
            font-weight: 600;
            color: var(--primary-color);
            margin: 0;
        }
        .error-title {
            font-size: 1.5rem;
            font-weight: 500;
            color: var(--secondary-color);
            margin-top: 0.5rem;
        }
        .error-message {
            margin-top: 1rem;
            max-width: 400px;
        }
        .back-home-button {
            display: inline-block;
            margin-top: 2rem;
            padding: 0.75rem 1.5rem;
            background-color: var(--primary-color);
            color: var(--bg-color);
            text-decoration: none;
            font-weight: 500;
            border-radius: 8px;
            transition: background-color 0.2s ease-in-out;
        }
        .back-home-button:hover {
            background-color: #5aa9d1; 
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="error-code">503</h1>
        <h2 class="error-title">Serviço Indisponível</h2>
        <p class="error-message">
            Opa! O serviço esta indisponível. Nossa equipe já foi notificada e está trabalhando para corrigir.
        </p>
        <a href="{{ url('/dashboard') }}" class="back-home-button">
            Voltar para o Início
        </a>
    </div>
</body>
</html>