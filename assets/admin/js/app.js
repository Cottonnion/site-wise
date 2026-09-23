(function($) {
    'use strict';

    const wsal = window.wsal_app_data || {};
    const $view = $('#wsal-view');
    let mediaFrame;

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

        // Clear Logs (with scope conditions)
        $(document).on('click', '[data-action="clear-logs"]', function() {
            openClearLogsModal();
        });

        $(document).on('click', '[data-dismiss="clear-modal"]', function() {
            $('#wsal-clear-modal').removeClass('is-visible');
        });

        $(document).on('change', '#wsal-clear-scope', function() {
            refreshClearCount();
        });

        $(document).on('click', '#wsal-clear-confirm', function() {
            const $btn = $(this);
            if ($btn.hasClass('is-busy')) return;

            const payload = {
                action: 'wsal_clear_logs',
                _wpnonce: wsal.nonce,
                scope: $btn.data('scope'),
                days: $btn.data('days') || 30,
            };

            $btn.addClass('is-busy').prop('disabled', true);

            $.post(wsal.ajax_url, payload, function(res) {
                if (!res.success) {
                    alert(res.data && res.data.message ? res.data.message : 'Failed to clear logs');
                    $btn.removeClass('is-busy').prop('disabled', false);
                    return;
                }

                $('#wsal-clear-modal').removeClass('is-visible');
                const message = res.data.message || 'Logs cleared';
                loadView('log', {}, {
                    after: function() {
                        $('#wsal-view').prepend(
                            '<div class="wsal-notice wsal-notice-success">' + $('<div>').text(message).html() + '</div>'
                        );
                    }
                });
            }).fail(function() {
                $btn.removeClass('is-busy').prop('disabled', false);
                alert('Failed to clear logs');
            });
        });

        function openClearLogsModal() {
            if (!$('#wsal-clear-modal').length) {
                const scopes = wsal.clear_scopes || [{ value: 'all', label: 'All logs' }];
                const options = scopes.map(function(s) {
                    return '<option value="' + s.value + '">' + $('<div>').text(s.label).html() + '</option>';
                }).join('');

                $('body').append(
                    '<div class="wsal-modal-overlay" id="wsal-clear-modal" role="presentation">' +
                    '<div class="wsal-modal" role="dialog" aria-modal="true" aria-labelledby="wsal-clear-title">' +
                        '<div class="wsal-modal-header">' +
                            '<h3 id="wsal-clear-title" class="wsal-modal-title">' + $('<div>').text(wsal.clear_logs_title || 'Clear activity logs').html() + '</h3>' +
                            '<button type="button" class="wsal-modal-close" data-dismiss="clear-modal" aria-label="Close">&times;</button>' +
                        '</div>' +
                        '<div class="wsal-modal-body">' +
                            '<label class="wsal-label" for="wsal-clear-scope">' + $('<div>').text(wsal.clear_logs_scope || 'Scope').html() + '</label>' +
                            '<select id="wsal-clear-scope" class="wsal-select">' + options + '</select>' +
                            '<p class="wsal-clear-count"></p>' +
                        '</div>' +
                        '<div class="wsal-modal-footer">' +
                            '<button type="button" class="wsal-btn wsal-btn-ghost" data-dismiss="clear-modal">' + $('<div>').text(wsal.clear_logs_cancel || 'Cancel').html() + '</button>' +
                            '<button type="button" class="wsal-btn wsal-btn-danger" id="wsal-clear-confirm">' + $('<div>').text(wsal.clear_logs_confirm || 'Clear logs').html() + '</button>' +
                        '</div>' +
                    '</div></div>'
                );
            }

            refreshClearCount();
            $('#wsal-clear-modal').addClass('is-visible');
        }

        function refreshClearCount() {
            const $count = $('.wsal-clear-count');
            const scope = $('#wsal-clear-scope').val() || 'all';
            const parts = scope.split('_');
            const days = parts[0] === 'older' ? (parseInt(parts[1], 10) || 30) : 0;

            $count.text(wsal.clear_logs_loading || 'Counting…');

            $.post(wsal.ajax_url, {
                action: 'wsal_clear_logs',
                _wpnonce: wsal.nonce,
                scope: parts[0] === 'older' ? 'older' : 'all',
                days: days,
                dry_run: 1
            }, function(res) {
                if (!res.success) {
                    $count.text('—');
                    return;
                }

                const n = parseInt(res.data.count, 10) || 0;
                $count.text(n > 0
                    ? (wsal.clear_logs_count || 'This will remove %1$d log entries.').replace('%1$d', n)
                    : (wsal.clear_logs_count_zero || 'No log entries match this criteria.')
                );

                $('#wsal-clear-confirm').data('scope', parts[0] === 'older' ? 'older' : 'all').data('days', days);
            });
        }

        $(document).on('click', '[data-action="copy-report-link"]', function() {
            const $btn = $(this);
            const original = $btn.data('copy-original') || $btn.text();

            copyToClipboard($btn.data('url') || '');

            $btn.data('copy-original', original);
            $btn.addClass('wsal-btn-copied');
            $btn.text(wsal.copied_msg || 'Link copied!');

            clearTimeout($btn.data('copy-timeout'));
            $btn.data('copy-timeout', setTimeout(function() {
                $btn.removeClass('wsal-btn-copied');
                $btn.text(original);
            }, 5000));
        });

        // Media Library Picker for Agency Logo
        $(document).on('click', '#wsal-choose-logo', function(e) {
            e.preventDefault();

            if (typeof wp === 'undefined' || !wp.media) {
                return;
            }

            if (mediaFrame) {
                mediaFrame.open();
                return;
            }

            mediaFrame = wp.media({
                title: 'Select or Upload Agency Logo',
                button: {
                    text: 'Use as Agency Logo'
                },
                multiple: false,
                library: {
                    type: 'image'
                }
            });

            mediaFrame.on('select', function() {
                const attachment = mediaFrame.state().get('selection').first().toJSON();
                const url = attachment.url;
                $('#wsal-agency-logo').val(url);
                $('#wsal-logo-preview').attr('src', url).show();
                $('#wsal-logo-preview-wrap').removeClass('is-empty').show();
                $('#wsal-remove-logo').show();
            });

            mediaFrame.open();
        });

        $(document).on('click', '#wsal-remove-logo', function(e) {
            e.preventDefault();
            $('#wsal-agency-logo').val('');
            $('#wsal-logo-preview').attr('src', '').hide();
            $('#wsal-logo-preview-wrap').addClass('is-empty').hide();
            $(this).hide();
        });

        // Settings Accordion (collapsible sections)
        $(document).on('click', '.wsal-acc-toggle', function() {
            const $section = $(this).closest('.wsal-acc');
            const $accordion = $section.closest('.wsal-accordion');
            const willOpen = !$section.hasClass('is-open');

            $accordion.find('.wsal-acc').removeClass('is-open');
            $accordion.find('.wsal-acc-toggle').attr('aria-expanded', 'false');

            if (willOpen) {
                $section.addClass('is-open');
                $(this).attr('aria-expanded', 'true');
            }
        });

        // Test Email / Webhook
        $(document).on('click', '#wsal-test-email', function() {
            runTestAction(this, {
                action: 'wsal_test_email',
                email: $('#wsal-digest-email').val()
            });
        });

        $(document).on('click', '#wsal-test-webhook', function() {
            runTestAction(this, {
                action: 'wsal_test_webhook',
                url: $('#wsal-webhook-url').val()
            });
        });

        function runTestAction(btn, payload) {
            const $btn = $(btn);
            if ($btn.hasClass('is-busy')) return;

            $btn.addClass('is-busy').prop('disabled', true);
            $('#wsal-settings-notice').empty();

            payload._wpnonce = wsal.nonce;

            $.post(wsal.ajax_url, payload, function(res) {
                const message = res.success ? res.data.message : (res.data && res.data.message ? res.data.message : 'Request failed');
                const cls = res.success ? 'wsal-notice-success' : 'wsal-notice-error';
                $('#wsal-settings-notice').html(
                    '<div class="wsal-notice ' + cls + '">' + $('<div>').text(message).html() + '</div>'
                );
            }).fail(function() {
                $('#wsal-settings-notice').html(
                    '<div class="wsal-notice wsal-notice-error">Request failed</div>'
                );
            }).always(function() {
                $btn.removeClass('is-busy').prop('disabled', false);
            });
        }

        // Settings Form Submit
        $(document).on('submit', '#wsal-settings-form', function(e) {
            e.preventDefault();
            const $form = $(this);
            const $btn = $form.find('button[type="submit"]').prop('disabled', true);

            const formData = {
                action: 'wsal_save_settings',
                _wpnonce: wsal.nonce,
                retention_days: $form.find('input[name="retention_days"]').val(),
                enable_report: $form.find('input[name="enable_report"]').is(':checked') ? 1 : 0,
                report_period: $form.find('select[name="report_period"]').val(),
                agency_name: $form.find('input[name="agency_name"]').val(),
                agency_logo_url: $form.find('input[name="agency_logo_url"]').val(),
                brand_color: $form.find('input[name="brand_color"]').val(),
                custom_footer_text: $form.find('input[name="custom_footer_text"]').val(),
                webhook_url: $form.find('input[name="webhook_url"]').val(),
                enable_email_digest: $form.find('input[name="enable_email_digest"]').is(':checked') ? 1 : 0,
                digest_email: $form.find('input[name="digest_email"]').val(),
                digest_frequency: $form.find('select[name="digest_frequency"]').val(),
            };

            $.post(wsal.ajax_url, formData, function(res) {
                $btn.prop('disabled', false);
                const message = res.success ? res.data.message : (res.data && res.data.message ? res.data.message : 'Error saving settings');
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
