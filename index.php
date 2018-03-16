<!DOCTYPE html>
<html>
<head>
<title>Missing Television Episodes</title>
<script type="text/javascript" src="/js/jquery-1.4.2.js"></script>
<script type="text/javascript" src="/js/jquery.tablesorter.min.js"></script>
<script type="text/javascript" src="/js/jquery.treeTable.min.js"></script>
<link rel="stylesheet" href="/css/tv.css" type="text/css" />
<link rel="stylesheet" href="/css/bootstrap.min.css" type="text/css" />
<script type="text/javascript">

 function SubmitForm() {
	var show_id = "";
	var show = "";
	var lang = "";

	let elems = document.querySelectorAll("#showSelector > option");
	for (i in elems) {
		if (elems[i].innerHTML.startsWith("&nbsp;")) {
			if (elems[i].selected) {
				show = elems[i].innerHTML.substring(24);
				show_id = elems[i].value;
				break;
			}
		} else {
			lang = elems[i].value;
		}
	}

	if (show_id === "" || show === "" || lang === "")
		return;

	$("#resultpart").html('<tr><td /><td><center><div class="loader"></div></center></td><td /></tr>');
	$.post('results.php', { show_id: show_id, show: show, lang: lang},
		function(data) {
			$('#resultpart').html(data);
	});
	return false;
 }

</script>
</head>
<body>

<div class="row">
<div style="margin: auto;">
<form class="form-inline">
<?php
include_once('tvclass.php');
echo '
	<div class="form-group mb-2 mt-2">
		<label for="showSelector" class="mr-4">Select a TVShow: </label>
		<select class="form-control" id="showSelector">';
		foreach(TVAnalyzer::GetUserShows() as $key => $value)
		{
			echo '<option value="'.$value.'">'.$key.'</option>';
		}
echo '		</select>
		<input value="Submit" type="button" name="getshows" id="getshows" class="btn btn-primary ml-2" onClick="SubmitForm(); return false;" />
	</div>';
?>
</form>
</div>
</div>

<div class="row">
<div class="col-md-1"></div>
<div class="col-md-10">
<table cellspacing="0" id="tvtable" class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>Name</th>
            <th>Episode Name</th>
            <th>Available</th>
        </tr>
    </thead>
    <tbody id="resultpart">
    </tbody>
</table>
</div>
</body>
</html>
