(function($) {
    'use strict';

    const wsal = window.wsal_app_data || {};
    const $view = $('#wsal-view');

    function loadView(view, data, opts) {
        opts = opts || {};

        const payload = $.extend({}, data || {}, {
            action: 'wsal_load_view',
            view: view,
            _wpnonce: wsal.nonce,
        });

        $view.addClass('wsal-loading');
        if (typeof opts.before === 'function') {
            opts.before();
        }

        $.post(wsal.ajax_url, payload, function(res) {
            $view.removeClass('wsal-loading');
            if (!res.success) {
                renderError(res.data && res.data.message ? res.data.message : 'Error loading view');
                return;
            }
            $view.html(res.data.html);
            setActiveTab(view);
            if (typeof opts.after === 'function') {
                opts.after();
            }
        }).fail(function() {
            $view.removeClass('wsal-loading');
            renderError('Error loading view');
        });
    }

    function setActiveTab(view) {
        $('.wsal-tab').each(function() {
            const isActive = $(this).data('view') === view;
            $(this).toggleClass('is-active', isActive);
            $(this).attr('aria-selected', isActive ? 'true' : 'false');
        });
    }

    function renderError(message) {
        $view.html(
            '<div class="wsal-notice wsal-notice-error">' +
            $('<div>').text(message).html() +
            '</div>'
        );
        setActiveTab('dashboard');
    }

    function copyToClipboard(text) {
        const el = document.createElement('textarea');
        el.value = text;
        el.setAttribute('readonly', '');
        el.style.position = 'absolute';
        el.style.left = '-9999px';
        document.body.appendChild(el);
        el.select();
        document.execCommand('copy');
        document.body.removeChild(el);
    }

    $(document).ready(function() {
        $('.wsal-tab').on('click', function(e) {
            e.preventDefault();
            loadView($(this).data('view'));
        });

        $(document).on('submit', '#wsal-log-filter', function(e) {
            e.preventDefault();
            const $form = $(this);
            loadView('log', {
                search: $form.find('input[name="search"]').val(),
                event_code: $form.find('select[name="event_code"]').val(),
            });
        });

        $(document).on('click', '.wsal-paginate', function(e) {
            e.preventDefault();
            const $form = $('#wsal-log-filter');
            loadView('log', {
                paged: $(this).data('page'),
                search: $form.find('input[name="search"]').val() || '',
                event_code: $form.find('select[name="event_code"]').val() || '',
            });
        });

        $(document).on('click', '[data-action="export-csv"]', function() {
            const params = new URLSearchParams({
                action: 'wsal_export_csv',
                _wpnonce: wsal.nonce,
                search: $('input[name="search"]').val() || '',
                event_code: $('select[name="event_code"]').val() || '',
            });
            window.location.href = wsal.ajax_url + '?' + params.toString();
        });

        $(document).on('click', '[data-action="copy-report-link"]', function() {
            copyToClipboard($(this).data('url') || '');
            alert(wsal.copied_msg || 'Report link copied to clipboard!');
        });

        $(document).on('submit', '#wsal-settings-form', function(e) {
            e.preventDefault();
            const $form = $(this);
            const $btn = $form.find('button[type="submit"]').prop('disabled', true);

            $.post(wsal.ajax_url, {
                action: 'wsal_save_settings',
                _wpnonce: wsal.nonce,
                retention_days: $form.find('input[name="retention_days"]').val(),
                enable_report: $form.find('input[name="enable_report"]').is(':checked') ? 1 : 0,
                report_period: $form.find('select[name="report_period"]').val(),
            }, function(res) {
                $btn.prop('disabled', false);
                const message = res.success ? res.data.message : (res.data && res.data.message);
                const cls = res.success ? 'wsal-notice-success' : 'wsal-notice-error';
                $('#wsal-settings-notice').html(
                    '<div class="wsal-notice ' + cls + '">' + $('<div>').text(message).html() + '</div>'
                );
            }).fail(function() {
                $btn.prop('disabled', false);
                $('#wsal-settings-notice').html(
                    '<div class="wsal-notice wsal-notice-error">Error saving settings</div>'
                );
            });
        });

        loadView(wsal.default_view || 'dashboard');
    });

})(jQuery);