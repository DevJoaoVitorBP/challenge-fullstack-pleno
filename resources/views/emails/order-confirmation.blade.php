<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px 20px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
        }
        .content {
            padding: 40px 20px;
        }
        .order-info {
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        .info-label {
            font-weight: bold;
            color: #333;
        }
        .info-value {
            color: #666;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .items-table th {
            background-color: #667eea;
            color: white;
            padding: 12px;
            text-align: left;
        }
        .items-table td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
        }
        .items-table tbody tr:hover {
            background-color: #f9f9f9;
        }
        .totals {
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 4px;
            margin: 20px 0;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-size: 16px;
        }
        .total-row.grand-total {
            font-size: 18px;
            font-weight: bold;
            color: #667eea;
            border-top: 2px solid #ddd;
            padding-top: 10px;
            margin-top: 10px;
        }
        .footer {
            background-color: #f5f5f5;
            padding: 20px;
            text-align: center;
            color: #666;
            font-size: 12px;
        }
        .tracking-info {
            background-color: #e8f5e9;
            border-left: 4px solid #4caf50;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .tracking-info strong {
            color: #2e7d32;
        }
        .cta-button {
            display: inline-block;
            background-color: #667eea;
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 4px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>✓ Pedido Confirmado!</h1>
            <p>Obrigado pela sua compra</p>
        </div>

        <div class="content">
            <p>Olá <strong>{{ $user->name }}</strong>,</p>
            <p>Seu pedido foi confirmado com sucesso e já está sendo processado. Abaixo está o resumo da sua compra:</p>

            <div class="order-info">
                <div class="info-row">
                    <span class="info-label">Número do Pedido:</span>
                    <span class="info-value">#{{ $order->id }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Data do Pedido:</span>
                    <span class="info-value">{{ $order->created_at->format('d/m/Y H:i') }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Status:</span>
                    <span class="info-value" style="color: #4caf50; font-weight: bold;">{{ ucfirst($order->status) }}</span>
                </div>
            </div>

            <h3>Itens do Pedido</h3>
            <table class="items-table">
                <thead>
                    <tr>
                        <th>Produto</th>
                        <th>Quantidade</th>
                        <th>Preço Unitário</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($orderItems as $item)
                        <tr>
                            <td>{{ $item->product->name }}</td>
                            <td style="text-align: center;">{{ $item->quantity }}</td>
                            <td>R$ {{ number_format($item->unit_price, 2, ',', '.') }}</td>
                            <td>R$ {{ number_format($item->unit_price * $item->quantity, 2, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="totals">
                <div class="total-row">
                    <span>Subtotal:</span>
                    <span>R$ {{ number_format($order->subtotal, 2, ',', '.') }}</span>
                </div>
                <div class="total-row">
                    <span>Impostos:</span>
                    <span>R$ {{ number_format($order->tax, 2, ',', '.') }}</span>
                </div>
                <div class="total-row">
                    <span>Envio:</span>
                    <span>R$ {{ number_format($order->shipping_cost, 2, ',', '.') }}</span>
                </div>
                <div class="total-row grand-total">
                    <span>Total:</span>
                    <span>R$ {{ number_format($order->total, 2, ',', '.') }}</span>
                </div>
            </div>

            <div class="tracking-info">
                <strong>📦 Informações de Rastreamento:</strong><br>
                Número da Nota Fiscal: <strong>{{ $invoiceNumber }}</strong><br>
                Código de Rastreamento: <strong>{{ $trackingNumber }}</strong><br>
                <small>Você poderá acompanhar seu pedido usando o código de rastreamento acima.</small>
            </div>

            <h3>Endereço de Entrega</h3>
            @php
                $addr = is_array($order->shipping_address)
                    ? $order->shipping_address
                    : json_decode($order->shipping_address, true);
            @endphp
            <p>
                {{ $addr['street'] ?? '' }}<br>
                {{ $addr['city'] ?? '' }} - {{ $addr['state'] ?? '' }}<br>
                CEP: {{ $addr['zip'] ?? '' }}
            </p>

            <p style="margin-top: 30px;">
                Se tiver alguma dúvida ou precisar de ajuda, não hesite em entrar em contato conosco.
            </p>

            <p style="text-align: center;">
                <a href="https://ecommerce.local" class="cta-button">Acompanhar Pedido</a>
            </p>
        </div>

        <div class="footer">
            <p>&copy; {{ date('Y') }} E-commerce. Todos os direitos reservados.</p>
            <p>Este é um email automático, por favor não responda.</p>
        </div>
    </div>
</body>
</html>