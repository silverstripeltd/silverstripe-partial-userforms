// Document ready
$(function() {
    // Handles checkboxes changes after reload
    setTimeout(function(){
        $(".userform :checked").each(function() {
            var id = $(this).prop('name');
            $(".userform [name='"+id+"']").trigger('click');
        });
    }, 300);


    // Handles jumping to steps when there is an error.
    $('.userform').on('userform.action.next', function () {
        var $step = $('.error-list li').closest('.form-step').first();
        if (!$step.length) {
            return;
        }
        var $userform = $step.closest('.userform').data('inst');
        $.each($userform.steps, function (i, step) {
            if (step.$el.attr('id') === $step.attr('id')) {
                $userform.jumpToStep(i);
                setTimeout(function(){ window.scrollTo(0, 0); }, 300);
            }
        });
    });
});
