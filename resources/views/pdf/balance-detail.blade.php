<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle de Cuentas por Cobrar</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 20px;
        }
        .header {
            margin-bottom: 30px;
        }
        .title {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .client-info {
            margin-bottom: 20px;
        }
        .client-info p {
            margin: 5px 0;
        }
        .total-box {
            border: 1px solid #000;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .total-label {
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 5px;
        }
        .total-amount {
            font-size: 18px;
            font-weight: bold;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th {
            background-color: #f0f0f0;
            color: #000;
            padding: 8px;
            text-align: left;
            font-weight: bold;
            font-size: 10px;
            border: 1px solid #000;
        }
        td {
            padding: 6px;
            border: 1px solid #000;
            font-size: 10px;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .footer {
            margin-top: 30px;
            font-size: 10px;
            color: #000;
            text-align: center;
        }
        .observa {
            word-wrap: break-word;
            word-break: break-word;
            white-space: normal;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">Detalle de Cuentas por Cobrar</div>
        <div class="client-info">
            <p><strong>Cliente:</strong> {{ $client['cli_des'] ?? 'N/A' }}</p>
            <p><strong>Código:</strong> {{ $client['co_cli'] ?? 'N/A' }}</p>
            <p><strong>Fecha de Generación:</strong> {{ $fechaGeneracion }}</p>
        </div>
    </div>

    <div class="total-box">
        <div class="total-label">Saldo Total</div>
        <div class="total-amount">${{ number_format($totalSaldo, 2, '.', ',') }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Vendedor</th>
                <th>Tipo Doc</th>
                <th>Nro Doc</th>
                <th>Fec Emis</th>
                <th>Fec Entrega</th>
                <th>Fec Venc</th>
                <th>Observa</th>
                <th class="text-right">Monto Net</th>
                <th class="text-right">Saldo</th>
            </tr>
        </thead>
        <tbody>
            @forelse($balanceDetail as $item)
                <tr>
                    <td>{{ $item->co_ven ?? '' }}</td>
                    <td>{{ $item->tipo_doc ?? '' }}</td>
                    <td>{{ $item->nro_doc ?? '' }}</td>
                    <td>{{ $item->fec_emis ? date('d/m/Y', strtotime($item->fec_emis)) : '' }}</td>
                    <td>{{ $item->fec_entrega ?? '' }}</td>
                    <td>{{ $item->fec_venc ? (is_string($item->fec_venc) ? date('d/m/Y', strtotime($item->fec_venc)) : $item->fec_venc) : '' }}</td>
                    <td class="observa">{{ $item->observa ?: 'Sin observaciones' }}</td>
                    <td class="text-right">${{ number_format(floatval($item->monto_net ?? 0), 2, '.', ',') }}</td>
                    <td class="text-right"><strong>${{ number_format(floatval($item->saldo ?? 0), 2, '.', ',') }}</strong></td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center">No hay documentos pendientes para este cliente</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Total de documentos: {{ count($balanceDetail) }}</p>
        <p>Generado el {{ $fechaGeneracion }}</p>
    </div>
</body>
</html>

