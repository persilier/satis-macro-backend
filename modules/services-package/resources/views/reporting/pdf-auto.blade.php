<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Reporting Réclamation</title>
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
            <img src="{{ $logo }}" alt="logo" style="height: 3.5em; border-radius: .1em; border:1px solid #F3F3F3;">
        </div>
        <div class="text-center" style="font-size: 10px;font-weight: bold">Rapport périodique de gestion des réclamations </div>
        <div class="text-center" style="font-size: 8px">Période : {{ $periode['libellePeriode'] }}</div>

    </div>


    @isset($statistiqueObject)
        @php
            $total = 0;
            $incomplete = 0;
            $toAssignementToUnit = 0;
            $toAssignementToStaff = 0 ;
            $awaitingTreatment = 0 ;
            $toValidate = 0 ;
            $toMeasureSatisfaction = 0;
            $percentage =0;
        @endphp
        <div style="width: 100%;margin-top: 30px">
            <div class="titre-stat">Statistiques des types de réclamations collectées</div>
            <div style="font-size: 7px">Légende: R. = RÉCLAMATIONS</div>
            <table class="table">
                <thead style="background: {{ $color_table_header }};font-size: 0.398em">
                <tr>
                    <th>CATÉGORIES DE RÉCLAMATION </th>
                    <th>OBJETS DE RÉCLAMATION</th>
                    <th>R. COLLECTÉES</th>
                    <th>R. INCOMPLETES</th>
                    <th>R. A ASSIGNER A UNE UNITE</th>
                    <th>R. A ASSIGNER A UN AGENT</th>
                    <th>R. A TRAITER</th>
                    <th>R. A VALIDER</th>
                    <th>R. A MESURER LA SATISFACTION</th>
                    <th>% RESOLUES</th>
                </tr>
                </thead>
                <tbody class="tbody_claim_object tbtable">

                @foreach ($statistiqueObject as $key => $value)
                    {{
                       $count = $value->claimObjects->count()
                    }}
                    @if($count > 0)

                        @foreach ($value->claimObjects as $keyObject => $valueObject)
                            <tr style="border-bottom: 0.1em solid #F3F3F3">
                                @if($keyObject === 0 )
                                    <td rowspan="{{ $count }}" class="uppercase" style="border-right: 0.1em solid #F3F3F3; background-color: #F3F3F3">
                                        {{ htmlspecialchars($value->name) }}
                                    </td>
                                @endif
                                <td class="capitalize">{{ htmlspecialchars($valueObject->name) }}</td>
                                <td>{{ $valueObject->total }}</td>
                                <td>{{ $valueObject->incomplete }}</td>
                                <td>{{ $valueObject->toAssignementToUnit }}</td>
                                <td>{{ $valueObject->toAssignementToStaff }}</td>
                                <td>{{ $valueObject->awaitingTreatment }}</td>
                                <td>{{ $valueObject->toValidate }}</td>
                                <td>{{ $valueObject->toMeasureSatisfaction }}</td>
                                <td>{{ $valueObject->percentage}} %</td>
                            </tr>

                            @php
                                $total += $valueObject->total;
                                $incomplete += $valueObject->incomplete;
                                $toAssignementToUnit += $valueObject->toAssignementToUnit;
                                $toAssignementToStaff+= $valueObject->toAssignementToStaff;
                                $awaitingTreatment += $valueObject->awaitingTreatment;
                                $toValidate += $valueObject->toValidate;
                                $toMeasureSatisfaction += $valueObject->toMeasureSatisfaction;
                                $percentage +=  $valueObject->percentage/$valueObject->total ;
                            @endphp

                        @endforeach

                    @endif
                @endforeach

                    <tr style="border-bottom: 0.1em solid #F3F3F3; background-color: #F3F3F3;font-weight: bold">
                        <td colspan="2" style="text-align: center">Total</td>
                        <td>{{ $total }}</td>
                        <td>{{ $incomplete }}</td>
                        <td>{{ $toAssignementToUnit }}</td>
                        <td>{{ $toAssignementToStaff }}</td>
                        <td>{{ $awaitingTreatment }}</td>
                        <td>{{ $toValidate }}</td>
                        <td>{{ $toMeasureSatisfaction }}</td>
                        <td>@if(($total == 0 || $percentage == 0)) 0 @else {{  $percentage/$total }}@endif % </td>
                    </tr>
                </tbody>
            </table>
        </div>
    @endisset

    @if(!empty($statistiqueQualificationPeriod))
        <div style="margin-top: 20px; width: 100%">
            <div class="titre-stat">Délai de qualification des réclamations </div>
            <table class="table">
                <thead style="background: {{ $color_table_header }};font-size: 0.4em">
                <tr>
                    <th>DÉLAI QUALIFICATION (EN JOURS)</th>
                    @foreach($statistiqueQualificationPeriod as $delaiValue)
                        <th>{{  $delaiValue['libelle'] }}</th>
                    @endforeach
                </tr>
                </thead>
                <tbody class="tbody_claim_object tbtable">
                <tr>
                    <td>Nombre</td>
                    @foreach($statistiqueQualificationPeriod as $delaiValue)
                        <td>{{  $delaiValue['total'] }}</td>
                    @endforeach
                </tr>
                <tr>
                    <td>Taux (%)</td>
                    @foreach($statistiqueQualificationPeriod as $delaiValue)
                        <td>{{  $delaiValue['pourcentage'] }}</td>
                    @endforeach
                </tr>
                </tbody>
            </table>
        </div>
    @endif

    @if(!empty($statistiqueTreatmentPeriod))
        <div style="width: 100%;margin-top: 20px">
            <div class="titre-stat">Délai de traitement des réclamations </div>
            <table class="table">
                <thead style="background: {{ $color_table_header }};font-size: 0.4em">
                <tr>
                    <th>DÉLAI TRAITEMENT (EN JOURS)</th>
                    @foreach($statistiqueTreatmentPeriod as $delaiValue)
                        <th>{{  $delaiValue['libelle'] }}</th>
                    @endforeach
                </tr>
                </thead>
                <tbody class="tbody_claim_object tbtable">
                <tr>
                    <td>Nombre</td>
                    @foreach($statistiqueTreatmentPeriod as $delaiValue)
                        <td>{{  $delaiValue['total'] }}</td>
                    @endforeach
                </tr>
                <tr>
                    <td>Taux (%)</td>
                    @foreach($statistiqueTreatmentPeriod as $delaiValue)
                        <td>{{  $delaiValue['pourcentage'] }}</td>
                    @endforeach
                </tr>
                </tbody>
            </table>
        </div>
    @endif

    @if(!empty($chanelGraph))
        <div style="margin-bottom: 1em; width: 100%">
            <div class="titre-stat" >Utilisation des canaux de déclaration des réclamations </div>
            <div style="padding-top: 1em; text-align: center">

                <img src="{{ $chanelGraph['image'] }}" alt="logo" style="height: 125px;">

            </div>
            <div style="text-align: center; font-size: 8px; margin-top: 2em; text-decoration: underline">
                Pourcentage d'utilisation des canneaux
            </div>
        </div>
    @endif

    @if(!empty($evolutionClaim))

        <div style="margin-bottom: 1em; width: 100%">
            <div class="titre-stat">Evolution des réclamations par @if($evolutionClaim['type_graphe']==='months') mois @elseif($evolutionClaim['type_graphe']==='weeks') semaines @else jours @endif </div>

            <div style="padding-top: 1em; text-align: center;margin: auto;">

                <img src="{{ $evolutionClaim['image'] }}" alt="logo" style="height: 175px;">

            </div>
            <div style="text-align: center; font-size: 8px; margin-top: 2em; text-decoration: underline">
                Evolution des réclamations
            </div>
        </div>

    @endif


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
