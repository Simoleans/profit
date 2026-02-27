<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Orden #{{ $order->fact_num }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #ffffff;
        }
        .container {
            background-color: #ffffff;
            border: 1px solid #ddd;
            border-radius: 4px;
            overflow: hidden;
        }
        .header {
            background-color: #f8f9fa;
            color: #333;
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid #ddd;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: bold;
        }
        .header p {
            margin: 10px 0 0 0;
            color: #666;
        }
        .content {
            padding: 20px;
        }
        .order-info {
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 20px;
        }
        .order-info h2 {
            color: #333;
            margin: 0 0 15px 0;
            font-size: 18px;
        }
        .info-row {
            margin-bottom: 8px;
            padding: 5px 0;
        }
        .info-label {
            font-weight: bold;
            color: #333;
        }
        .info-value {
            color: #333;
        }
        .items-section {
            margin-bottom: 20px;
        }
        .items-section h3 {
            color: #333;
            margin: 0 0 15px 0;
            font-size: 16px;
        }
        .item {
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            padding: 12px;
            margin-bottom: 8px;
        }
        .item-name {
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }
        .item-details {
            color: #666;
            font-size: 14px;
        }
        .total-section {
            background-color: #f8f9fa;
            border: 2px solid #333;
            padding: 15px;
            text-align: center;
        }
        .total-section h3 {
            margin: 0 0 10px 0;
            font-size: 16px;
        }
        .total-amount {
            font-size: 24px;
            font-weight: bold;
            margin: 0;
        }
        .footer {
            background-color: #f8f9fa;
            padding: 15px;
            text-align: center;
            color: #666;
            font-size: 12px;
            border-top: 1px solid #ddd;
        }
        .status-badge {
            display: inline-block;
            padding: 2px 8px;
            border: 1px solid #333;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .promotion-badge {
            background-color: #333;
            color: #fff;
            padding: 2px 6px;
            font-size: 10px;
            font-weight: bold;
            margin-left: 8px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>¡Nueva Orden Recibida!</h1>
            <p>Gracias por confiar en nosotros</p>
        </div>

        <div class="content">
            <div class="order-info">
                <h2>Información de la Orden</h2>
                <div class="info-row">
                    <span class="info-label">Número de Orden:</span>
                    <span class="info-value">#{{ $order->fact_num }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Fecha de Emisión:</span>
                    <span class="info-value">{{ \Carbon\Carbon::parse($order->fec_emis)->format('d/m/Y') }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Fecha de Vencimiento:</span>
                    <span class="info-value">{{ \Carbon\Carbon::parse($order->fec_venc)->format('d/m/Y') }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Estado:</span>
                    <span class="info-value">
                        <span class="status-badge status-pending">
                            @switch($order->status)
                                @case('P') Pendiente @break
                                @case('PEN') Pendiente @break
                                @case('A') Aprobado @break
                                @case('APR') Aprobado @break
                                @case('R') Procesado @break
                                @case('PRO') Procesado @break
                                @case('E') Entregado @break
                                @case('ENT') Entregado @break
                                @default {{ $order->status }}
                            @endswitch
                        </span>
                    </span>
                </div>
                @if($order->descrip)
                <div class="info-row">
                    <span class="info-label">Descripción:</span>
                    <span class="info-value">{{ $order->descrip }}</span>
                </div>
                @endif
            </div>

            <div class="items-section">
                <h3>Artículos de la Orden</h3>
                @foreach($order->rows as $row)
                <div class="item">
                    <div class="item-name">
                        {{ $row->article->art_des }}
                        @if($row->promotion == 1)
                            <span class="promotion-badge">EN PROMOCIÓN</span>
                        @endif
                    </div>
                    <div class="item-details">
                        <span>Código: {{ $row->article->co_art }}  | Precio: ${{ number_format($row->prec_vta, 2, ',', '.') }}</span>
                        <span><strong>${{ number_format($row->reng_neto, 2, ',', '.') }}</strong></span>
                    </div>
                </div>
                @endforeach
            </div>

            <div class="total-section">
                <h3>Total de la Orden</h3>
                <p class="total-amount">${{ number_format($order->tot_neto, 2, ',', '.') }}</p>
                <p style="margin: 10px 0 0 0; color: #333;">
                    {{ $order->rows->count() }} artículo{{ $order->rows->count() !== 1 ? 's' : '' }}
                </p>
            </div>

            @if($order->comentario)
            <div style="margin-top: 20px; padding: 12px; background-color: #f8f9fa; border: 1px solid #ddd;">
                <h4 style="margin: 0 0 8px 0; color: #333;">Comentarios:</h4>
                <p style="margin: 0; color: #333;">{{ $order->comentario }}</p>
            </div>
            @endif

          {{--   @if($order->dir_ent)
            <div style="margin-top: 20px; padding: 12px; background-color: #f8f9fa; border: 1px solid #ddd;">
                <h4 style="margin: 0 0 8px 0; color: #333;">Dirección de Entrega:</h4>
                <p style="margin: 0; color: #333;">{{ $order->dir_ent }}</p>
            </div>
            @endif --}}
        </div>

        <div class="footer">
            <p>Este correo fue enviado automáticamente por nuestro sistema.</p>
            <p>Si tiene alguna pregunta sobre su orden, no dude en contactarnos.</p>
            <p style="margin-top: 15px; font-size: 12px; opacity: 0.8;">
                Generado el {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}
            </p>
        </div>
    </div>
</body>
</html>
