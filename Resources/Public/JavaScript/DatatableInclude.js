define('TYPO3/CMS/Backend/Modal',[
    'jquery',
    'TYPO3/CMS/Backend/Modal',
    'TYPO3/CMS/AtBackup/Datatables'
], function ($, Model) {
    $('.at-datatable').DataTable({
        order: [],
        language: {
            lengthMenu: "Display _MENU_ records per page",
            zeroRecords: "Nothing found - sorry",
            info: "Showing page _PAGE_ of _PAGES_",
            infoEmpty: "No records available",
            infoFiltered: "(filtered from _MAX_ total records)",
            paginate: {
                previous: '<<',
                next: '>>'
            },
        }
    });
});