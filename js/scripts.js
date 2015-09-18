//JQuery
$(function() {
	//Executes when DOM loaded
	$(document).ready(function() {

		//Ajax request for Roster
		var rosterPromise = $.ajax({
			type: 'GET',
			dataType: 'text',
			url: '../files/roster.csv',
			headers: {
				'Access-Control-Allow-Headers': '*',
				'Access-Control-Allow-Origin': '*',
				'Access-Control-Allow-Methods': '*'
			},
			cache: false
		});

		//Run when GET action is completed successfully
		rosterPromise.done(function(data) {
			//Read line by line
			var dataArray = data.split("\n");

			//For each line, populate it like this --> <tr> <td> text </td> </tr>
			$.each(dataArray, function() {
				if(this != "") {
					var row = new String("");
					var valArray = this.split(",");
					row += "<tr>";

					$.each(valArray, function() {
						row += "<td>" + this + "</td>";
					});

					row += "</tr>";

					$('#roster-body').append(row);
				}
			});

			//Transform plain table into DataTables
			$('#roster-table').DataTable({
				"sDom": '<"toolbar">frltip'
			});

			$('#roster-table_length').css('float', 'left'); //Align length toggle to left
			$('#roster-table_filter').css('float', 'right'); //Align search to right
			$('#roster-table_wrapper').css('font-size','0.8em'); //Size the entire data table
			$('#roster-table').css('text-align','left'); //Align table data to left
			$('div.toolbar').html("OUR TEAM").css('font-size','1.4em'); //Title of the table
		});

		//Failed GET
		rosterPromise.fail(function() {
			$('#roster-table').append("<tr><td>Failed to load roster</td><td></td>/tr>").css('font-size','0.8em');
		});

	});
});