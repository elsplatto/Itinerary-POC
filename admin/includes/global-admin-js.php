<script>
$(function(){
    $('body').on('click', '.showHide', function(e) {
        var target = $($(this).attr('data-target'));
        if ($(this).prop('tagName').toLowerCase() === 'input')
        {
            if (target.is(':hidden'))
            {
                target.show();
            }
            else
            {
                target.hide();
            }
        }
        else if ($(this).prop('tagName').toLowerCase() === 'a')
        {
            e.preventDefault();
            var target = $($(this).attr('data-target'));
            var hideText = $(this).attr('data-hideText');
            var showText = $(this).attr('data-showText');
            if (target.is(':hidden'))
            {
                target.show();
                $(this).text(hideText);
            }
            else
            {
                target.hide();
                $(this).text(showText);
            }
        }
    })
 });
</script>