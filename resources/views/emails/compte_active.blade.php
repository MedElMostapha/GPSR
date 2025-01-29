<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0">
    <title>Compte Activé</title>
    <style>
        /* Basic reset */
        body,
        h1,
        p {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }

        /* Container */
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 8px;
        }

        /* Header */
        .header {
            text-align: center;
            padding: 20px 0;
        }

        .header img {
            width: 150px;
            height: auto;
        }

        /* Content */
        .content {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .content h1 {
            font-size: 24px;
            color: #2c3e50;
            margin-bottom: 20px;
        }

        .content p {
            font-size: 16px;
            color: #555;
            margin-bottom: 15px;
        }

        /* Footer */
        .footer {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
            color: #777;
        }

        .footer a {
            color: #3498db;
            text-decoration: none;
        }

        .footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <div class="email-container">
        <!-- Header with Logo -->
        <div class="header">
            <img src="{{ $message->embed(public_path('assets/images/logo.png')) }}"
                alt="Logo de l'application">
        </div>

        <!-- Content -->
        <div class="content">
            <h1>Bonjour {{ $user->name }},</h1>
            <p>Nous sommes ravis de vous informer que votre compte a été activé avec succès.</p>
            <p>Vous pouvez maintenant vous connecter et profiter de tous les services que nous offrons.</p>
            <p>Si vous avez des questions ou besoin d'aide, n'hésitez pas à nous contacter.</p>

            <a href="{{ url('/login') }}"
                class="btn">Se Connecter</a>
        </div>


        <!-- Footer -->
        <div class="footer">
            <p>Cordialement,</p>
            <p>L'équipe de <strong>GPSR</strong></p>
            <p>

            </p>
        </div>
    </div>
</body>

</html>