@extends('app')

@section('content')
<h2>
    @lang('note.upcoming.title')
    <a href="{!! route('upcoming', ['mode' => 'no-expired']) !!}" class="btn btn-default btn-sm">
        @lang('note.upcoming.no-expired')
    </a>
</h2>

<div class="jscroll-container">
    @each('note.single', $notes, 'note')

    {!! $notes->render() !!}
</div>
@stop

@section('footer')
<script>
$('.pager').hide();
$('.jscroll-container').jscroll({
    loadingHtml: '<i class="fa fa-spinner fa-spin"></i>',
    padding: 10,
    nextSelector: '.pager a[rel="next"]',
    contentSelector: '.jscroll-container',
    callback: function () {
        $('.pager').hide();
    }
});
</script>
@stop