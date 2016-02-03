<script type="text/javascript">
    var ue = UE.getEditor('editor');
    ue.ready(function() {
        ue.execCommand('serverparam', {
            '_token': '{{ csrf_token() }}',
        });
    });
</script>