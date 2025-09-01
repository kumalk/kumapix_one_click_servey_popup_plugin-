(function( $ ) {
	'use strict';

	$(function() {

        // Color Picker
        $('.kocs-color-picker').wpColorPicker();

        // Trigger logic
        function toggleTriggerDelay() {
            if ($('#kocs_trigger').val() === 'timed') {
                $('.kocs-timed-delay').show();
            } else {
                $('.kocs-timed-delay').hide();
            }
        }
        toggleTriggerDelay();
        $('#kocs_trigger').on('change', toggleTriggerDelay);

        // Chart.js
        if ($('#kocs-chart').length > 0) {
            var ctx = document.getElementById('kocs-chart').getContext('2d');
            new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: kocs_chart_data.labels,
                    datasets: [{
                        label: '# of Votes',
                        data: kocs_chart_data.data,
                        backgroundColor: [
                            'rgba(255, 99, 132, 0.7)',
                            'rgba(54, 162, 235, 0.7)',
                            'rgba(255, 206, 86, 0.7)',
                            'rgba(75, 192, 192, 0.7)',
                            'rgba(153, 102, 255, 0.7)',
                            'rgba(255, 159, 64, 0.7)'
                        ],
                        borderColor: [
                            'rgba(255, 99, 132, 1)',
                            'rgba(54, 162, 235, 1)',
                            'rgba(255, 206, 86, 1)',
                            'rgba(75, 192, 192, 1)',
                            'rgba(153, 102, 255, 1)',
                            'rgba(255, 159, 64, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        title: {
                            display: true,
                            text: 'Survey Answer Distribution'
                        }
                    }
                }
            });
        }
        
        // Datepicker
        $('.datepicker').datepicker({
            dateFormat: 'yy-mm-dd'
        });

        // CSV Export
        $('#kocs-export-csv').on('click', function(e) {
            e.preventDefault();
            var button = $(this);
            var spinner = button.next('.spinner');

            button.prop('disabled', true);
            spinner.addClass('is-active');
            
            var startDate = $('#start_date').val();
            var endDate = $('#end_date').val();

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'kocs_export_csv',
                    nonce: '<?php echo wp_create_nonce("kocs_export_nonce"); ?>',
                    start_date: startDate,
                    end_date: endDate
                },
                xhrFields: {
                    responseType: 'blob'
                },
                success: function(blob, status, xhr) {
                     var filename = "";
                    var disposition = xhr.getResponseHeader('Content-Disposition');
                    if (disposition && disposition.indexOf('attachment') !== -1) {
                        var filenameRegex = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/;
                        var matches = filenameRegex.exec(disposition);
                        if (matches != null && matches[1]) filename = matches[1].replace(/['"]/g, '');
                    }

                    if (typeof window.navigator.msSaveBlob !== 'undefined') {
                        window.navigator.msSaveBlob(blob, filename);
                    } else {
                        var URL = window.URL || window.webkitURL;
                        var downloadUrl = URL.createObjectURL(blob);

                        if (filename) {
                            var a = document.createElement("a");
                            if (typeof a.download === 'undefined') {
                                window.location.href = downloadUrl;
                            } else {
                                a.href = downloadUrl;
                                a.download = filename;
                                document.body.appendChild(a);
                                a.click();
                            }
                        } else {
                            window.location.href = downloadUrl;
                        }

                        setTimeout(function () { URL.revokeObjectURL(downloadUrl); }, 100);
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                     alert('An error occurred during export.');
                },
                complete: function() {
                    button.prop('disabled', false);
                    spinner.removeClass('is-active');
                }
            });
        });
	});

})( jQuery );
