<!DOCTYPE html>
<html>
<head>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 40px 20px;
            background-color: #f8f9fa;
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
        .message {
            color: #333;
            margin-bottom: 25px;
        }
        .welcome-box {
            background-color: #f8f9fa;
            border-left: 4px solid #87CEEB;
            padding: 15px;
            margin: 20px 0;
        }
        .footer {
            text-align: center;
            color: #666;
            margin-top: 30px;
            font-size: 14px;
        }
        .logo {
            color: #87CEEB;
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 20px;
        }
        .highlight {
            color: #87CEEB;
            font-weight: bold;
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
                Benvingut/da {{ $user->name }}!
            </div>
            
            <div class="message">
                Gràcies per registrar-te a <span class="highlight">Grupify</span>. Estem molt contents de tenir-te entre nosaltres! El teu compte ja està actiu i pots començar a gaudir de tots els serveis immediatament.
            </div>
            
            <div class="welcome-box">
                <strong>Amb el teu compte de Grupify podràs:</strong><br>
                • Crear i gestionar grups<br>
                • Connectar amb altres membres<br>
                • Accedir a totes les funcionalitats <br>
            </div>
            
            <div class="message">
                Si no has creat aquest compte, si us plau, contacta amb el nostre equip de suport.
            </div>
            
            <div class="footer">
                Gràcies per unir-te a <span class="highlight">Grupify</span>.<br>
                Esperem que gaudeixis de la teva experiència amb nosaltres!
            </div>
        </div>
    </div>
</body>
</html>