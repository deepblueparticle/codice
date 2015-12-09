@extends('app')

@section('content')
<h2 class="page-heading">@lang('note.create.title')</h2>

{!! BootForm::open()->action(route('note.create')) !!}
    <div class="row">
        <div class="col-md-9">
            {!! BootForm::textarea(trans('note.labels.content'), 'content')->required()->autofocus() !!}
        </div>
        <div class="col-md-3 well">
            {!! BootForm::text(trans('note.labels.expires_at'), 'expires_at')->placeholder(trans('note.datetime-placeholder'))->helpBlock(trans('note.optional-field')) !!}

            {!! BootForm::text(trans('note.labels.reminder_email'), 'reminder_email')->placeholder(trans('note.datetime-placeholder'))->helpBlock(trans('note.optional-field')) !!}

            {!! BootForm::text(trans('note.labels.reminder_smsapi'), 'reminder_smsapi')->placeholder(trans('note.datetime-placeholder'))->helpBlock(trans('note.optional-field'))->disabled() !!}
        </div>
    </div>

    <div class="form-group">
        <label class="control-label" for="labels">@lang('note.labels.labels')</label>
        <select name="labels[]" class="form-control" id="labels" multiple>
        @foreach ($labels as $id => $label)
            <option value="{{ $id }}">{{ $label }}</option>
        @endforeach
        </select>
    </div>

    {!! BootForm::submit(trans('note.create.submit'), 'btn-primary') !!}
{!! BootForm::close() !!}
@stop

@section('footer')
<script>
$("#labels").select2({
    placeholder: "@lang('note.labels.labels-select')",
    tags: true,
    theme: "bootstrap",
});
</script>
@stop
