<script>
$(function() {
    $('#txtTitle').blur(function(e) {
        var title = $(this).val().toLowerCase();
        title = title.replace(/[^a-zA-Z\d\s:]/g,'').replace(/ /g, '-');
        if ($('#txtPageName').val() == '')
        {
            $('#txtPageName').val(title);
        }
    });

    $('#txtPageName').blur(function(e)
    {
        var el = $(this);
        var pagename = el.val();
        var id = 0;
        if ($('#itineraryId').length > 0)
        {
            id = $('#itineraryId').val();
        }
        if (pagename.length > 0)
        {
            pagename = pagename.replace(/[^a-zA-Z\d\s:,'-]/g,'').replace(/ /g, '-');
            el.val(pagename);
        }
        var indicator = el.siblings('.ajaxCheck');
        if (pagename !== '')
        {
            indicator.css({
                top: $(this).parent('label').height() / 2,
                left: $(this).outerWidth() + 10
            });
            //indicator.text('checking...');

            $.ajax({
                type: 'POST',
                url: 'services/unique-pagename.php',
                dataType: 'json',
                data: {
                    pagename: pagename,
                    postedId: id
                },
                beforeSend: function()
                {
                    beforeUniqueCheckHandler(el);
                },
                success: function(data)
                {
                    successUniqueCheckHandler(data, el, indicator)
                }
            });
        }
        else
        {
            el.siblings('small').text('Please enter a page name');
        }
    });

    function beforeUniqueCheckHandler(el) {
        el.siblings('.ajaxCheck').addClass('preloader');
    }

    function successUniqueCheckHandler(data,el,indicator)
    {
        var obj = JSON.parse(data);

        el.siblings('.ajaxCheck').removeClass('preloader');

        if (obj.success)
        {
            if (obj.unique)
            {
                //indicator.text('unique');
                el.siblings('.ajaxCheck').addClass('okay');
                el.siblings('.ajaxCheck').removeClass('problem');
                el.removeAttr('data-invalid');
                el.parent('label').removeClass('error');
                el.siblings('small').text('Please enter a page name');
            }
            else
            {
                //indicator.text('duplicate');
                el.siblings('.ajaxCheck').addClass('problem');
                el.siblings('.ajaxCheck').removeClass('okay');
                el.attr('data-invalid','');
                el.parent('label').addClass('error');
                el.siblings('small').text('Your page name is not unique');
            }
        }
    }
});
</script>