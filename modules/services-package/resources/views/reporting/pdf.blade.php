<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <title>Reporting Réclamation</title>
        <!-- Bootstrap core CSS -->
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">

        <style>
            .tbtable tr:nth-child(even) td {
                background-color: #F3F3F3;
            }

            .title_tableau{
                font-size: 10px;
                text-align: left;
                padding-bottom: 0.5em;
            }

            .text-right {
                text-align: right;
            }
            .text-center {
                text-align: center;
            }

            .dot {
                height: 0.8em;
                width: 0.8em;
                border-radius: 25%;
                display: inline-block;
                margin-bottom: 0.3em;
            }

            .dot-1 {
                height: 0.8em;
                width: 0.8em;
                border-radius: 25%;
                display: block;
                margin-bottom: 0.3em;
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

            .title-center{
                text-align: center;
                font-weight: bold;
            }


        </style>

    </head>
    <body style="background: white">
    <main>
        <div  class="text-center">
            <img src="{{ $logo }}" alt="logo" style="height: 4em">
        </div>
        <div class="text-center" style="font-size: 12px;font-weight: bold">Rapport périodique de gestion des réclamations </div>
        <div class="text-center" style="font-size: 10px">Période : {{ $periode['startDate'] }} au {{ $periode['endDate'] }}</div>
        <div style="margin-bottom: .3em;border-bottom: 1px solid #F3F3F3 ">&nbsp;</div>

        @if($statistiqueObject['data'])
            <div class="title_tableau title-center" >Statistiques des types de réclamations collectées</div>
            <div class="title_tableau">Légende: R. = RÉCLAMATIONS</div>
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
                    @foreach ($statistiqueObject['data'] as $key => $value)
                        {{
                           $count = count($value['claim_objects'])
                        }}
                        @if($count > 1)

                            @foreach ($value['claim_objects'] as $keyObject => $valueObject)
                                <tr style="border-bottom: 0.1em solid #F3F3F3">
                                    @if($keyObject === 0 )
                                        <td rowspan="{{ $count }}" class="uppercase" style="border-right: 0.1em solid #F3F3F3; background-color: #F3F3F3">
                                            {{ htmlspecialchars($value['name'][$lang]) }}
                                        </td>
                                    @endif
                                    <td class="capitalize">{{ htmlspecialchars($valueObject['name'][$lang]) }}</td>
                                    <td>{{ $valueObject['total'] }}</td>
                                    <td>{{ $valueObject['incomplete'] }}</td>
                                    <td>{{ $valueObject['toAssignementToUnit'] }}</td>
                                    <td>{{ $valueObject['toAssignementToStaff'] }}</td>
                                    <td>{{ $valueObject['awaitingTreatment'] }}</td>
                                    <td>{{ $valueObject['toValidate'] }}</td>
                                    <td>{{ $valueObject['toMeasureSatisfaction'] }}</td>
                                    <td>{{ $valueObject['percentage'] }} %</td>
                                </tr>
                            @endforeach

                        @endif
                    @endforeach

                    @if($statistiqueObject['total'])

                        <tr style="border-bottom: 0.1em solid #F3F3F3; background-color: #F3F3F3;font-weight: bold">
                            <td colspan="2" style="text-align: center">Total</td>
                            <td>{{ $statistiqueObject['total']['totalCollect'] }}</td>
                            <td>{{ $statistiqueObject['total']['totalIncomplete'] }}</td>
                            <td>{{ $statistiqueObject['total']['totalToAssignUnit'] }}</td>
                            <td>{{ $statistiqueObject['total']['totalToAssignStaff'] }}</td>
                            <td>{{ $statistiqueObject['total']['totalAwaitingTreatment'] }}</td>
                            <td>{{ $statistiqueObject['total']['totalToValidate'] }}</td>
                            <td>{{ $statistiqueObject['total']['totalToMeasureSatisfaction'] }}</td>
                            <td>{{ $statistiqueObject['total']['totalPercentage'] }} %</td>
                        </tr>

                    @endif

                </tbody>
            </table>
        @endif
        @if($statistiqueQualificationPeriod)
            <div style="margin-bottom: 1em">&nbsp;</div>
            <div class="title_tableau title-center" >Délai de qualification des réclamations </div>
            <table class="table">
                <thead style="background: {{ $color_table_header }};font-size: 0.4em">
                    <tr>
                        <th>DÉLAI QUALIFICATION (EN JOURS)</th>
                        <th>0-2 JOURS</th>
                        <th>2-4 JOURS</th>
                        <th>4-6 JOURS</th>
                        <th>6-10 JOURS</th>
                        <th>PLUS DE 10 JOURS</th>
                    </tr>
                </thead>
                <tbody class="tbody_claim_object tbtable">
                    <tr>
                        <td>Nombre</td>
                        <td>{{ $statistiqueQualificationPeriod['0-2']['total'] }}</td>
                        <td>{{ $statistiqueQualificationPeriod['2-4']['total'] }}</td>
                        <td>{{ $statistiqueQualificationPeriod['4-6']['total'] }}</td>
                        <td>{{ $statistiqueQualificationPeriod['6-10']['total'] }}</td>
                        <td>{{ $statistiqueQualificationPeriod['+10']['total'] }}</td>
                    </tr>
                    <tr>
                        <td>Taux (%)</td>
                        <td>{{ $statistiqueQualificationPeriod['0-2']['pourcentage'] }}</td>
                        <td>{{ $statistiqueQualificationPeriod['2-4']['pourcentage'] }}</td>
                        <td>{{ $statistiqueQualificationPeriod['4-6']['pourcentage'] }}</td>
                        <td>{{ $statistiqueQualificationPeriod['6-10']['pourcentage'] }}</td>
                        <td>{{ $statistiqueQualificationPeriod['+10']['pourcentage'] }}</td>
                    </tr>
                </tbody>
            </table>
        @endif

        @if($statistiqueTreatmentPeriod)
            <div style="margin-top: 1em">&nbsp;</div>
            <div class="title_tableau title-center">Délai de traitement des réclamations </div>
            <table class="table">
                <thead style="background: {{ $color_table_header }};font-size: 0.4em">
                    <tr>
                        <th>DÉLAI TRAITEMENT (EN JOURS)</th>
                        <th>0-2 JOURS</th>
                        <th>2-4 JOURS</th>
                        <th>4-6 JOURS</th>
                        <th>6-10 JOURS</th>
                        <th>PLUS DE 10 JOURS</th>
                    </tr>
                </thead>
                <tbody class="tbody_claim_object tbtable">
                    <tr>
                        <td>Nombre</td>
                        <td>{{ $statistiqueTreatmentPeriod['0-2']['total'] }}</td>
                        <td>{{ $statistiqueTreatmentPeriod['2-4']['total'] }}</td>
                        <td>{{ $statistiqueTreatmentPeriod['4-6']['total'] }}</td>
                        <td>{{ $statistiqueTreatmentPeriod['6-10']['total'] }}</td>
                        <td>{{ $statistiqueTreatmentPeriod['+10']['total'] }}</td>
                    </tr>
                    <tr>
                        <td>Taux (%)</td>
                        <td>{{ $statistiqueTreatmentPeriod['0-2']['pourcentage'] }}</td>
                        <td>{{ $statistiqueTreatmentPeriod['2-4']['pourcentage'] }}</td>
                        <td>{{ $statistiqueTreatmentPeriod['4-6']['pourcentage'] }}</td>
                        <td>{{ $statistiqueTreatmentPeriod['6-10']['pourcentage'] }}</td>
                        <td>{{ $statistiqueTreatmentPeriod['+10']['pourcentage'] }}</td>
                    </tr>
                </tbody>
            </table>
        @endif

        @if($statistiqueChannel)
            <div style="margin-bottom: 1em">&nbsp;</div>
            <div class="title_tableau title-center">Utilisation des canaux de déclaration des réclamations </div>
            <table class="table">
                <thead style="background: {{ $color_table_header }};font-size: 0.4em">
                    <tr>
                        @for($n = 0; $n < count($statistiqueChannel['name']); $n++)
                            <th> {{ $statistiqueChannel['name'][$n] }}</th>
                        @endfor
                    </tr>
                </thead>
                <tbody class="tbody_claim_object tbtable">
                    <tr>
                        @for($n = 0; $n < count($statistiqueChannel['total_claim']); $n++)
                            <td> {{ $statistiqueChannel['total_claim'][$n] }}</td>
                        @endfor
                    </tr>
                    <tr>
                        @for($n = 0; $n < count($statistiqueChannel['total_pourcentage']); $n++)
                            <td> {{ $statistiqueChannel['total_pourcentage'][$n] }}</td>
                        @endfor
                    </tr>
                </tbody>
            </table>
        @endif

        @if($chanelGraph)
            <div style="display: block">
                <div style="margin-bottom: 1em">&nbsp;</div>
                <div class="title_tableau title-center" >Utilisation des canaux de déclaration des réclamations </div>
                <div class="row" style="padding-top: 1em">
                    <div class="col-xs-7">
                        <img src="{{ $chanelGraph['image'] }}" alt="logo" style="margin-left: 10em">
                    </div>

                    <div class="col-xs-4" style="padding-top: 1em">
                        @for($n = 0; $n < count($chanelGraph['libelle']); $n++)
                            <div> <span style="background-color:{{ $chanelGraph['color'][$n] }}" class="dot"></span>
                                <span style="font-size: 8px; font-weight: bold;">{{ $chanelGraph['libelle'][$n] }}</span>
                            </div>
                        @endfor
                    </div>
                </div>
                <div style="text-align: center; font-size: 10px; margin-top: 1em; text-decoration: underline">
                    Pourcentage d'utilisation des canneaux
                </div>
            </div>
        @endif

        @if($evolutionClaim)
            <div style="border-bottom: 1px solid #F3F3F3; padding-bottom: 2em">
                <div style="margin-bottom: 1em">&nbsp;</div>
                <div class="title_tableau title-center">Evolution des réclamations par @if($evolutionClaim['type']==='months') mois @elseif($evolutionClaim['type']==='weeks') semaines @else jours @endif </div>
                <div class="row" style="padding-top: 1em">
                    <div class="col-xs-12">
                        <img src="{{ $evolutionClaim['image'] }}" alt="logo" style="width: 100%">
                    </div>
                </div>
                <div style="margin-left: 15em; margin-right: 15em">
                    <span style="background-color:{{ $evolutionClaim['legend']['claims_received'] }}" class="dot"></span>
                    <span style="font-size: 11px;display: inline-block; margin-top: 1px; margin-right: .2em">Réclamations reçus</span>
                    <span style="background-color:{{ $evolutionClaim['legend']['claims_resolved'] }}" class="dot"></span>
                    <span style="font-size: 11px;display: inline-block; margin-top: 1px">Réclamations résolues</span>
                </div>
                <div style="text-align: center; font-size: 10px; margin-top: 2em; text-decoration: underline">
                    Evolution des réclamations
                </div>
            </div>
        @endif
    </main>
    <footer style="text-align: center; font-size: 6px;bottom: 0;position:fixed">
        Powered By Satis FinTech S.A
    </footer>
    </body>
</html>