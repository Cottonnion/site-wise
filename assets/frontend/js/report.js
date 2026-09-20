(function($) {
    'use strict';

    $(document).ready(function() {
        initReportInteractions();
    });

    function initReportInteractions() {
        const printBtn = $('[data-action="print-report"]');
        if (printBtn.length) {
            printBtn.on('click', function(e) {
                e.preventDefault();
                window.print();
            });
        }
    }

})(jQuery);
