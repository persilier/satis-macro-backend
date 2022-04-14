<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Reporting Réclamation</title>
    <!-- Bootstrap core CSS -->
    <link rel="stylesheet" href="{{ asset('assets/reporting/css/bootstrap.min.css') }}">
</head>
<body style="background: white">
<main>

    <div style="">

        <div class="text-center">
            <img src="{{ $logo }}" alt="logo" style="height: 3.5em; border-radius: .1em; border:1px solid #F3F3F3;">
        </div>
        <div class="text-center" style="font-size: 10px;font-weight: bold">Rapport périodique de gestion des réclamations </div>
        <div class="text-center" style="font-size: 8px">Période : {{ $data['libellePeriode'] }}</div>
        <br><br><br>

        <table class="table table-striped table-bordered">
            <thead>
                <tr>
                <th>Nº</th>
                <th>Produits ou services concernés</th>
                <th>Résumé synthétique de la réclamation</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td colspan="3" style="text-align: center; background: rgb(213, 216, 219); font-weight: bold;"> RÉCLAMATIONS RECUES AU COURS DU  {{ $data['libellePeriode'] }} </td>
            </tr>
            @if(count($data['receivedClaims'])>0)
            @foreach($data['receivedClaims'] as $claim)
                <tr>
                    <td>{{$loop->index}}</td>
                    <td>{{optional($claim->claimObject)->name}}</td>
                    <td>{!! nl2br($claim->description) !!}</td>
                </tr>
            @endforeach
            @else
                <td colspan="3"> Aucune reclamation </td>
            @endif
            <tr>
                <td colspan="3" style="text-align: center; background: rgb(213, 216, 219); font-weight: bold;"> RÉCLAMATIONS TRAITÉES AU COURS DU  {{ $data['libellePeriode'] }} </td>
            </tr>
            @if(count($data['treatedClaims'])>0)
                @foreach($data['treatedClaims'] as $claim)
                <tr>
                    <td>{{$loop->index}}</td>
                    <td>{{optional($claim->claimObject)->name}}</td>
                    <td>{!! nl2br($claim->description) !!}</td>
                </tr>
            @endforeach
            @else
                <td colspan="3"> Aucune reclamation </td>
            @endif
            <tr>
                <td colspan="3" style="text-align: center; background: rgb(213, 216, 219); font-weight: bold;"> RÉCLAMATIONS NON RÉSOLUES OU EN SUSPENS DU {{ $data['libellePeriode'] }} </td>
            </tr>
            @if(count($data['unresolvedClaims'])>0)
                @foreach($data['unresolvedClaims'] as $claim)
                <tr>
                    <td>{{$loop->index}}</td>
                    <td>{{optional($claim->claimObject)->name}}</td>
                    <td>{!! nl2br($claim->description) !!}</td>
                </tr>
            @endforeach
            @else
                <td colspan="3"> Aucune reclamation </td>
            @endif
            </tbody>
            <tfoot>
            <tr>
                <th>Nº</th>
                <th>Produits ou services concernés</th>
                <th>Résumé synthétique de la réclamation</th>
            </tr>
            </tfoot>
        </table>
    </div>



</main>
<footer style="position:fixed;bottom: 0;">
    <table class="table">
        <tr>
            <td><img src="{{ $logoSatis }}" alt="logo" style="height: 0.5em; width: 1em;margin-top: 0.389em"></td>
            <td style="text-align: center;font-size: 6px">Copyright {{ env('APP_YEAR_INSTALLATION', '2020') }}, SATIS</td>
            <td style="text-align: right;font-size: 6px">{{ date('d/m/Y') }}</td>
        </tr>
    </table>
</footer>
</body>
</html>
