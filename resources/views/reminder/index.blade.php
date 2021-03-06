@extends('app')

@section('content')
    <h2 class="page-heading warning">@lang('reminder.index.title')</h2>

    <div class="codice-container">
    @if (count($reminders))
    <table class="table">
        <thead>
            <tr>
                <th class="col-md-3">@lang('reminder.index.note-id')</th>
                <th class="col-md-4">@lang('reminder.index.remind-at')</th>
                <th class="col-md-3">@lang('reminder.index.type')</th>
                <th class="col-md-2">@lang('reminder.index.controls')</th>
            </tr>
        </thead>
        <tbody>
        @foreach ($reminders as $reminder)
            <tr>
                <td>
                    <a href="{!! route('note', ['id' => $reminder->note_id]) !!}">
                        {{ trans('reminder.index.note-link', ['id' => $reminder->note_id]) }}
                    </a>
                </td>
                <td>
                    {{ $reminder->remind_at->format(trans('app.datetime')) }}
                    ({{ $reminder->remind_at->diffForHumans() }})
                </td>
                <td>
                    {{ trans("reminder.services.{$reminder->type}") }}
                </td>
                <td>
                    <a href="{!! route('reminder.remove', ['id' => $reminder->id]) !!}" class="action" data-confirm="cancel">@icon('trash-o') @lang('reminder.index.cancel')</a>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    @else
    <h1 class="app-error">@lang('reminder.none.title')</h1>
    <h2 class="app-error">@lang('reminder.none.content')</h2>
    @endif
    </div>
@stop
