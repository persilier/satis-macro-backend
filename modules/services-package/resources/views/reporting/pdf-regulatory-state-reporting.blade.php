<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Reporting Réclamation</title>
    <!-- Bootstrap core CSS -->
    <!-- Bootstrap core CSS -->
    <link rel="stylesheet" href="{{ asset('assets/reporting/css/bootstrap.min.css') }}">

    <style>
        body{
            font-family: Poppins, Helvetica, sans-serif;

        }
        h1, h2, h3, h4, h5, h6 {
            margin-top: 0;
            margin-bottom: 0.5rem;
        }

        p {
            margin-top: 0;
            margin-bottom: 1rem;
        }
        /* titre des cadres*/
        .titre-stat{
            font-family: Poppins, Helvetica, sans-serif;
            font-size: 10px;
            font-weight: bold;
            text-align: center;
            margin-bottom: 10px;
        }

        .footer span {
            height: 0.5em;
            display: inline-block;
            font-size: 6px;
            vertical-align: middle;
        }

        .day-imp{

            text-align: right;
        }


        .tbtable tr:nth-child(even) td {
            background-color: #F3F3F3;
        }

        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }

        .lowercase {
            text-transform: lowercase;
        }
        .uppercase {
            text-transform: uppercase;
        }
        .capitalize {
            text-transform: capitalize;
        }

        .tbody_claim_object tr td{
            font-size: 8px;
        }

        thead tr {
            color: white;
        }

        footer .table tr, .table td {
            border-top: none !important;
            border-left: none !important;
        }

        footer .table tr td{
            vertical-align: bottom;
            width: 33.33%;
            margin: auto;
        }

        footer .table{
            width: 100%
        }

    </style>
</head>
<body style="background: white">
<main>

    <div style="">
        <div class="text-center">
            <img src="{{ $data['logo'] }}" alt="logo" style="height: 3.5em; border-radius: .1em; border:1px solid #F3F3F3;">
        </div>
        <div class="text-center" style="font-size: 10px;font-weight: bold">@if($data['title']) {{ $data['title']  }} @endif</div>
        <div class="text-center" style="font-size: 8px">Période : @if($data['libellePeriode']) {{ $data['libellePeriode'] }} @endif</div>
    </div>
    <div style="width: 100%;margin-top: 10px">
        <div class="titre-stat">@if($data['description']) {{ $data['description']  }} @endif</div>
        <table class="table">
            <thead style="background: {{ $data['colorTableHeader'] }};font-size: 0.4em">
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
                    <td style="width: auto">{!! nl2br($claim->description) !!}</td>
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
