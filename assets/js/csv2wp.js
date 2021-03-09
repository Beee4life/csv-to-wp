(function($) {
    $(document).ready(function() {

        var header_id = 'csv2wp-header';
        var key = '.csv2wp__key';
        var table_header = '.csv2wp__th';

        $('select.csv2wp_import_in').change(function () {

            $changed_value = $(this).val();

            if ( 'table' !== $changed_value ){
                $changed_value = $changed_value.substr(4);
                document.getElementById(header_id).checked = false;
            } else {
                document.getElementById(header_id).checked = true;
            }
            var changed_value = $changed_value;

            $(table_header).addClass('hidden');
            $(key).addClass('hidden');
            $(table_header + '--' + changed_value).removeClass('hidden');
            $(key + '--' + changed_value).removeClass('hidden');

        });

        $('.upload_button').click(function () {
            var type = $(this).data('type');
            $("#" + type).trigger('click');
        });

        $("input[type='file']").change(function () {
            var type = $(this).attr('id');
            $('.form--' + type + ' .val').text(this.value.replace(/C:\\fakepath\\/i, ''))
        });

    });
})(jQuery);
