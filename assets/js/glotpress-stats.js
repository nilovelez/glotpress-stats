$(document).ready(function () {
  $('#stats-table').DataTable( {
  	pageLength: 50,
  	searching: false,
  	lengthMenu: [ [25, 50, 100, -1], [25, 50, 100, "All"] ],
  	language: {
		"decimal":        GLOTSTATS_TRANSLATION.decimal,
		"emptyTable":     GLOTSTATS_TRANSLATION.emptyTable,
		"info":           GLOTSTATS_TRANSLATION.info,
		"infoEmpty":      GLOTSTATS_TRANSLATION.infoEmpty,
		"infoFiltered":   GLOTSTATS_TRANSLATION.infoFiltered,
		"thousands":      GLOTSTATS_TRANSLATION.thousands,
		"lengthMenu":     GLOTSTATS_TRANSLATION.lengthMenu,
		"loadingRecords": GLOTSTATS_TRANSLATION.loadingRecords,
		"processing":     GLOTSTATS_TRANSLATION.processing,
		"search":         GLOTSTATS_TRANSLATION.search,
		"zeroRecords":    GLOTSTATS_TRANSLATION.zeroRecords,
		"paginate": {
			"first":    GLOTSTATS_TRANSLATION.paginate_first,
			"last":     GLOTSTATS_TRANSLATION.paginate_last,
			"next":     GLOTSTATS_TRANSLATION.paginate_next,
			"previous": GLOTSTATS_TRANSLATION.paginate_previous
		},
		"aria": {
			"sortAscending":  GLOTSTATS_TRANSLATION.aria_sortAscending,
			"sortDescending": GLOTSTATS_TRANSLATION.aria_sortDescending
		}
	}
  } );
});