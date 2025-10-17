<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $notification->name }} - {{ $product->name }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333333;
            background-color: #f5f5f5;
            padding: 20px;
        }
        
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .email-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 40px 30px;
            text-align: center;
            color: white;
        }
        
        .email-header h1 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 8px;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .email-header p {
            font-size: 16px;
            opacity: 0.95;
            font-weight: 300;
        }
        
        .email-body {
            padding: 40px 30px;
        }
        
        .greeting {
            font-size: 18px;
            color: #2d3748;
            margin-bottom: 20px;
            font-weight: 500;
        }
        
        .product-card {
            background: linear-gradient(135deg, #f6f8fb 0%, #ffffff 100%);
            border-left: 4px solid #667eea;
            padding: 25px;
            margin: 25px 0;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        
        .product-name {
            font-size: 22px;
            font-weight: 700;
            color: #667eea;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .plant-icon {
            font-size: 28px;
        }
        
        .notification-title {
            font-size: 16px;
            font-weight: 600;
            color: #4a5568;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .notification-message {
            font-size: 15px;
            line-height: 1.8;
            color: #2d3748;
            background-color: #fff;
            padding: 18px;
            border-radius: 6px;
            border: 1px solid #e2e8f0;
        }
        
        .cta-button {
            display: inline-block;
            margin-top: 25px;
            padding: 14px 32px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 15px;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .cta-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(102, 126, 234, 0.5);
        }
        
        .email-footer {
            background-color: #f7fafc;
            padding: 30px;
            text-align: center;
            border-top: 1px solid #e2e8f0;
        }
        
        .footer-text {
            font-size: 14px;
            color: #718096;
            margin-bottom: 15px;
        }
        
        .footer-brand {
            font-size: 18px;
            font-weight: 700;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 8px;
        }
        
        .footer-tagline {
            font-size: 13px;
            color: #a0aec0;
            font-style: italic;
        }
        
        .divider {
            height: 1px;
            background: linear-gradient(to right, transparent, #e2e8f0, transparent);
            margin: 25px 0;
        }
        
        @media only screen and (max-width: 600px) {
            body {
                padding: 10px;
            }
            
            .email-header {
                padding: 30px 20px;
            }
            
            .email-header h1 {
                font-size: 24px;
            }
            
            .email-body {
                padding: 30px 20px;
            }
            
            .product-card {
                padding: 20px;
            }
            
            .product-name {
                font-size: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="email-header">
            <h1>🌿 {{ $notification->name }}</h1>
            <p>Rappel de soin pour votre plante</p>
        </div>
        
        <!-- Body -->
        <div class="email-body">
            <p class="greeting">Bonjour {{ $user->name }},</p>
            
            <p style="font-size: 15px; color: #4a5568; margin-bottom: 20px;">
                C'est le moment de prendre soin de votre plante préférée ! Voici un rappel important :
            </p>
            
            <div class="product-card">
                <div class="product-name">
                    <span class="plant-icon">🌱</span>
                    <span>{{ $product->name }}</span>
                </div>
                
                <div class="notification-title">
                    <span style="color: #667eea;">●</span>
                    {{ $notification->name }}
                </div>
                
                <div class="notification-message">
                    {{ $notification->description }}
                </div>
            </div>
            
            <div class="divider"></div>
            
            <p style="font-size: 14px; color: #718096; margin-bottom: 20px;">
                💡 <strong>Conseil :</strong> Prenez quelques minutes maintenant pour effectuer cette tâche. Vos plantes vous remercieront !
            </p>
            
            <div style="text-align: center;">
                <a href="{{ url('/shop/products/' . $product->id) }}" class="cta-button">
                    Voir ma plante 🌿
                </a>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="email-footer">
            <div class="footer-brand">UrbanGreen</div>
            <p class="footer-tagline">Cultivez votre passion, nourrissez la nature</p>
            
            <div style="margin-top: 20px;">
                <p class="footer-text">
                    Vous recevez cet email car vous suivez le produit <strong>{{ $product->name }}</strong>.
                </p>
                <p style="font-size: 12px; color: #a0aec0; margin-top: 10px;">
                    © {{ date('Y') }} UrbanGreen. Tous droits réservés.
                </p>
            </div>
        </div>
    </div>
</body>
</html>
