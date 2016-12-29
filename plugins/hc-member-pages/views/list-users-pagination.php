<?php

/**
* The paginator at the bottom of the list-users pages
* Expects:
* $l: type of users to show
* $s: search string
* $pn: page number
* $totalrows: number rows returned
* $limit: number of rows to show
**/

echo pmpro_getPaginationString($pn, $totalrows, $limit, 1, add_query_arg(array("s" => urlencode($s), "l" => $l, "limit" => $limit)));

