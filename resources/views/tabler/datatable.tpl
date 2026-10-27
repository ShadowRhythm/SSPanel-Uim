<link href="//cdn.datatables.net/v/bs5/dt-2.0.8/datatables.min.css" rel="stylesheet"/>
<script src="//cdn.datatables.net/v/bs5/dt-2.0.8/datatables.min.js"></script>

<script>
    let tableConfig = {
        autoWidth: false,
        iDisplayLength: 10,
        scrollX: true,
        columns: [
            {foreach $details['field'] as $key => $value}
            {
                data: '{$key}'
            },
            {/foreach}
        ],
        initComplete: function () {
            $('div.dt-length').parent().parent().removeClass('mt-2').addClass('row px-3 py-3')
            $('div.dt-scroll').parent().parent().removeClass('mt-2')
            $('div.dt-info').parent().parent().removeClass('mt-2').addClass('row card-footer')
            $('div.dt-length').parent().removeClass('col-md-auto me-auto').addClass('col-auto')
            $('div.dt-search').parent().removeClass('col-md-auto me-auto ms-auto').addClass('col-auto')
            $('div.dt-info').parent().removeClass('col-md-auto me-auto').addClass('col')
            $('div.dt-paging').parent().removeClass('col-md-auto me-auto ms-auto').addClass('col-auto')
            $('div.dt-scroll-body').css('border-bottom-style', 'none')
        },
        language: {
            "sProcessing": "{trans key='common.dt_processing' escape='js'}",
            "sLengthMenu": "{trans key='common.dt_length_menu' escape='js'}",
            "sZeroRecords": "{trans key='common.dt_zero_records' escape='js'}",
            "sInfo": "{trans key='common.dt_info' escape='js'}",
            "sInfoEmpty": "{trans key='common.dt_info_empty' escape='js'}",
            "sInfoFiltered": "{trans key='common.dt_info_filtered' escape='js'}",
            "sInfoPostFix": "",
            "sSearch": "<i class=\"ti ti-search\"></i> ",
            "sUrl": "",
            "sEmptyTable": "{trans key='common.dt_empty_table' escape='js'}",
            "sLoadingRecords": "{trans key='common.dt_loading_records' escape='js'}",
            "sInfoThousands": ",",
            "oPaginate": {
                "sFirst": "{trans key='common.dt_first' escape='js'}",
                "sPrevious": "<i class=\"ti ti-arrow-left\"></i>",
                "sNext": "<i class=\"ti ti-arrow-right\"></i>",
                "sLast": "{trans key='common.dt_last' escape='js'}"
            },
            "oAria": {
                "sSortAscending": "{trans key='common.dt_sort_ascending' escape='js'}",
                "sSortDescending": "{trans key='common.dt_sort_descending' escape='js'}"
            }
        }
    };
</script>
