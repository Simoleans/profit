<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catálogo de Artículos</title>
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
    </style>
</head>
<body>
    <div class="header">
        <div class="title">Artículos</div>
        <div style="font-size: 11px; color: #666; margin-bottom: 5px;">
            Fecha de este reporte: {{ $fechaGeneracion }}
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Código</th>
                <th>Descripción</th>
                <th>Línea</th>
                <th class="text-right">Precio Venta</th>
                {{-- <th class="text-right">Stock</th> --}}
            </tr>
        </thead>
        <tbody>
            @forelse($articles as $article)
                <tr>
                    <td style="font-family: monospace;">{{ $article->co_art }}</td>
                    <td>{{ $article->art_des }}</td>
                    <td>{{ $article->line->lin_des ?? 'Sin línea' }}</td>
                    <td class="text-right">${{ number_format(floatval($article->prec_vta1 ?? 0), 2, '.', ',') }}</td>
                    {{-- <td class="text-right">{{ number_format(floatval($article->stock_act ?? 0), 0, '.', ',') }}</td> --}}
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center">No hay artículos disponibles</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Total de artículos: {{ $totalArticulos }}</p>
        <p>Generado el {{ $fechaGeneracion }}</p>
    </div>
</body>
</html>

