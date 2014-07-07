(function ($) {
    "use strict";
    $.fn.confirmRemove = function (optionsList) {
        var defaults, options, obj, confirmHTML, removeClass;
        obj = $(this);
        removeClass = obj[0].classList[0];
        defaults = {
            targetClass: 'holder',
            confirmShellClass: 'confirm',
            confirmQuestion: 'Are you sure?',
            confirmButtonText: 'Yes',
            confirmButtonDefaultClass: 'btnConfirm',
            confirmButtonClasses: '',
            cancelButtonText: 'Cancel',
            cancelButtonDefaultClass: 'btnCancel',
            cancelButtonClasses: '',
            hideButtonOnClick: true,
            htmlURL: null ,
            onBeforeRemove : function () {}
        };
        options = $.extend(defaults, optionsList);

        //build shell HTML
        if (options.htmlURL === null) {
            confirmHTML = '<div class="' + options.confirmShellClass + '">';
            confirmHTML += '<p>' + options.confirmQuestion + '</p>';
            confirmHTML += '<a href="#" class="' + options.confirmButtonDefaultClass + ' ' + options.confirmButtonClasses + '">' + options.confirmButtonText + '</a>';
            confirmHTML += '<a href="#" class="' + options.cancelButtonDefaultClass + '  ' + options.cancelButtonClasses + '">' + options.cancelButtonText + '</a>';
            confirmHTML += '</div>';
        } else {
            $.get(options.htmlURL, function (data) {
                confirmHTML = data;
            });
        }

        obj.on('click', function (e) {
            var targetEl, eventTarget;
            e.preventDefault();
            eventTarget = e.target;
            if (options.hideButtonOnClick) {
                $(eventTarget).hide();
            }
            targetEl = $(this).closest('.' + options.targetClass);
            $(targetEl).append(confirmHTML);

            //attach click event to cancel button
            $('.' + options.cancelButtonDefaultClass).on('click', function (e) {
                e.preventDefault();
                $(this).closest('.' + options.targetClass).find('.' + removeClass).show();
                $(this).closest('.' + options.confirmShellClass).remove();
            });

            //attach click event to confirm button
            $('.' + options.confirmButtonDefaultClass).on('click', function (e) {
                e.preventDefault();
                $(this).closest('.' + options.targetClass).remove();
            });
        });
    };
})(jQuery);
