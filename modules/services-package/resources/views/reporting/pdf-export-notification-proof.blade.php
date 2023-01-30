@extends('ServicePackage::reporting.layouts.default')
@section('content')
    <div style="">
        <div class="text-center">
            <img src="{{ $data['logo'] }}" alt="logo" style="height: 3.5em; border-radius: .1em; border:1px solid #F3F3F3;">
        </div>
        <div class="text-center" style="font-size: 15px;font-weight: bold">@if($data['report_title']) {{ $data['report_title'] }} @endif</div>
        <div class="text-center" style="font-size: 12px">Période : @if($data['libellePeriode']) {{ $data['libellePeriode'] }} @endif</div>
    </div>
    <div style="width: 100%;margin-top: 10px">
        <table class="table table-striped" style="with:100%">
            <thead style="background: {{ $data['colorTableHeader']}}">
            <tr>
                <th>Destinataire</th>
                <th>Email</th>
                <th>Canal</th>
                <th>Contenu/message</th>
                <th>Date</th>
                <th>Statut</th>
            </tr>
            </thead>
            <tbody>

            @foreach($data['proof'] as $proof)
                <tr>
                    <td>
                        {{isset($proof['to']['firstname']) ? $proof['to']['firstname'].' '.$proof['to']['lastname']:'--'}}
                    </td>
                    <td>
                        {{ isset($proof['to']['email']) && !empty($proof['to']['email'])  ? $proof['to']['email'][0]: '-'}}
                    </td>
                    <td>{{$proof['channel']}}</td>
                    <td>{{$proof['message']}}</td>
                    <td>{{$proof['sent_at']}}</td>
                    <td>{{$proof['status']}}</td>
                </tr>
            @endforeach

            </tbody>
            <tfoot style="background: {{ $data['colorTableHeader'] }}">
            <tr>
                <th>Destinataire</th>
                <th>Email</th>
                <th>Canal</th>
                <th>Contenu/message</th>
                <th style="width: 150px">Date</th>
                <th>Statut</th>
            </tr>
            </tfoot>
        </table>
    </div>
@endsection