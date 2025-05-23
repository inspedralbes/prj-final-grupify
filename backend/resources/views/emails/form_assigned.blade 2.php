<!DOCTYPE html>
<html>
<head>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            background-color: #f8f9fa;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 40px 20px;
        }
        .email-content {
            background-color: white;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo {
            color: #87CEEB;
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 20px;
        }
        .message {
            color: #333;
            margin-bottom: 25px;
        }
        .highlight {
            color: #87CEEB;
            font-weight: bold;
        }
        .footer {
            text-align: center;
            color: #666;
            margin-top: 30px;
            font-size: 14px;
        }
        .note {
            background-color: #f8f9fa;
            border-left: 4px solid #87CEEB;
            padding: 15px;
            margin: 20px 0;
            font-style: italic;
            color: #555;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="email-content">
            <div class="header">
                <div class="logo">Grupify</div>
            </div>
            
            <div class="message">
                Hola <span class="highlight">{{ $userName }}</span>,
            </div>
            
            <div class="message">
                S'ha assignat un nou formulari al teu compte de <span class="highlight">Grupify</span>: <strong>{{ $formTitle }}</strong>.
            </div>
            
            <div class="message">
                Si us plau, accedeix a la plataforma i completa el formulari el més aviat possible.
            </div>
            
            <div class="note">
            Recorda que tens una data límit per completar aquest formulari!
            </div>
            
            <div class="footer">
                Salutacions,<br>
                L'equip de <span class="highlight">Grupify</span>
            </div>
        </div>
    </div>
</body>
</html>