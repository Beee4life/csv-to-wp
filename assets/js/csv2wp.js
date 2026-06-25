(function($) {
    $(function() {

        const keyClass = '.csv2wp__key';
        const tableHeaderClass = '.csv2wp__th';

        $('select.csv2wp_import_in').on('change', function () {
            const $this = $(this);
            let changedValue = $this.val();

            if ('table' !== changedValue && changedValue) {
                changedValue = changedValue.substr(4);
            }

            $(`${tableHeaderClass}--${changedValue}`).removeClass('hidden');
            $(`${keyClass}--${changedValue}`).removeClass('hidden');
        });

        $('.upload_button').on('click', function () {
            const type = $(this).data('type');
            $(`#${type}`).trigger('click');
        });

        $("input[type='file']").on('change', function () {
            const type = $(this).attr('id');
            const fileName = this.value.replace(/C:\\fakepath\\/i, '');
            $(`.form--${type} .val`).text(fileName);
        });

    });
})(jQuery);
