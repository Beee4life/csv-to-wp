(function($) {
    $(document).ready(function() {

        var header_id = 'csv2wp-header';
        var key = '.csv2wp__key';
        var table_header = '.csv2wp__th';

        $('select.csv2wp_import_in').change(function () {

            var changed_value = $(this).val();

            if ( 'table' !== changed_value ){
                var changed_value = changed_value.substr(4);
                document.getElementById(header_id).checked = false;
            } else {
                document.getElementById(header_id).checked = true;
            }
            console.log(changed_value);

            $(table_header).addClass('hidden');
            $(key).addClass('hidden');
            $(table_header + '--' + changed_value).removeClass('hidden');
            $(key + '--' + changed_value).removeClass('hidden');

        });
    });
})(jQuery);
