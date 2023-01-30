<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>RAPPORT PDF UEMOA</title>
    <!-- Bootstrap core CSS -->
    <link rel="stylesheet" href="{{ asset('assets/reporting/css/bootstrap.min.css') }}">

    <style>
        body {
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
        .titre-stat {
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

        .day-imp {

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

        .tbody_claim_object tr td {
            font-size: 8px;
        }

        thead tr {
            color: white;
        }

        footer .table tr, .table td {
            border-top: none !important;
            border-left: none !important;
        }

        footer .table tr td {
            vertical-align: bottom;
            width: 33.33%;
            margin: auto;
        }

        footer .table {
            width: 100%
        }

    </style>

</head>
<body style="background: white">
<main>

    <div style="">
        <div class="text-center">
            <img src="{{ $logo }}" alt="logo" style="height: 3.5em; border-radius: .1em; border:1px solid #F3F3F3;">
        </div>
        <div class="text-center" style="font-size: 10px;font-weight: bold">@if($title) {{ $title  }} @endif</div>
        <div class="text-center" style="font-size: 8px">Période
            : @if($libellePeriode) {{ $libellePeriode }} @endif</div>
    </div>
    <div style="width: 100%;margin-top: 10px">
        <div class="titre-stat">@if($description) {{ $description  }} @endif</div>
        <table class="table">
            <thead style="background: {{ $colorTableHeader }};font-size: 0.4em">
            <tr>
                @if(!$myInstitution)
                    <th>Filiale</th>
                @endif
                @if($relationShip)
                    <th>Relation avec le réclamant</th>
                @else
                    <th>Type Client</th>
                    <th>Client</th>
                    <th>N° compte</th>
                @endif
                <th>Téléphone</th>
                <th>Agence</th>
                <th>Catégorie réclamation</th>
                <th>Objet réclamation</th>
                <th>Canal de réception</th>
                <th>Unité de traitement</th>
                <th>Staff traitant</th>
                <th>Collecteur</th>
                <th>Unité concernée</th>
                <th>Pilote en charge</th>
                <th>Status</th>
            </tr>
            </thead>
            <tbody class="tbody_claim_object tbtable">
            @if($claims)

                @foreach($claims as $key =>  $claim)

                    <tr>
                        @if(!$myInstitution)
                            <td>{{ $claim['filiale'] }}</td>
                        @endif
                        @if($relationShip)
                            <td>{{ $claim['relationShip'] }}</td>
                        @else
                            <td>{{ $claim['typeClient'] }}</td>
                            <td>{{ $claim['client'] }}</td>
                            <td>{{ $claim['account'] }}</td>
                        @endif
                        <td>{{ $claim['telephone'] }}</td>
                        <td>{{ $claim['agence'] }}</td>
                        <td>{{ $claim['claimCategorie'] }}</td>
                        <td>{{ $claim['claimObject'] }}</td>
                        <td>{{ $claim['requestChannel'] }}</td>
                        <td>{{ $claim['functionTreating'] }}</td>
                        <td>{{ $claim['staffTreating'] }}</td>
                        <td>{{ $claim['collector_info'] }}</td>
                        <td>{{ $claim['unit_info'] }} </td>
                        <td> {{ $claim['pilot_in_charge_info'] }} </td>
                        <td>{{ $claim['status'] }}</td>
                    </tr>

                @endforeach

            @endif
            </tbody>
        </table>
    </div>

</main>
<footer style="position:fixed;bottom: 0;">
    <table class="table">
        <tr>
            <td><img src="{{ $logoSatis }}" alt="logo" style="height: 0.5em; width: 1em;margin-top: 0.389em"></td>
            <td style="text-align: center;font-size: 6px">Copyright {{ env('APP_YEAR_INSTALLATION', '2020') }}, Satis
                Fintech
            </td>
            <td style="text-align: right;font-size: 6px">{{ date('d/m/Y') }}</td>
        </tr>
    </table>
</footer>
</body>
</html>
