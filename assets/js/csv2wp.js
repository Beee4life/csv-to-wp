(function($) {
    $(document).ready(function() {

        var key = '.csv2wp_key';

        $('select.csv2wp_import_in').change(function () {

            const self = this;
            var row_id = this.id.substr(17);
            var header = 'td.header span.csv2wp_header-' + row_id;
            var changed_value = $(this).val();

            $('.csv2wp_key-' + row_id).addClass('hidden');
            $('#csv2wp_key_' + changed_value + '-' + row_id).removeClass('hidden');

            if ( 'table' !== changed_value ){
                document.getElementById("csv2wp_header-" + row_id).checked = false;
            } else {
                document.getElementById("csv2wp_header-" + row_id).checked = true;
            }
        });
    });
})(jQuery);
