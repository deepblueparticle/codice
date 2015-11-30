@extends('app')

@section('content')
    <h2><span class="label label-{{ $colors[$label->color] }}">@icon('tag') {{ $label->name }}</span></h2>

    @each('note.single', $notes, 'note')
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
