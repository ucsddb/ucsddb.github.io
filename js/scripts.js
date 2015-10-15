//JQuery
$(function() {
	//Executes when DOM loaded
	$(document).ready(function() {

		//Ajax request for Roster
		var rosterPromise = $.ajax({
			type: 'GET',
			dataType: 'text',
			url: 'files/roster2.csv',
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
			//If you run into TypeError: Cannot read 'mData' of undefined, change \r to \n
			var dataArray = data.split("\r");

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

			$('#roster-table_length').addClass('roster-table_length'); //Align length toggle to left
			$('#roster-table_filter').addClass('roster-table_filter'); //Align search to right
			$('#roster-table_wrapper').css('font-size','0.8em'); //Size the entire data table
			$('#roster-table').css('text-align','left'); //Align table data to left
			$('div.toolbar').html("OUR TEAM").css('font-size','1.4em'); //Title of the table
		});

		//Only put them on same line if screen is big enough
		$(window).resize(function() {
			if($(window).width() > 430) {
				$('#roster-table_length').addClass('roster-table_length'); //Align length toggle to left
				$('#roster-table_filter').addClass('roster-table_filter'); //Align search to right
			}
			else {
				$('#roster-table_length').removeClass('roster-table_length'); //Align length toggle to left
				$('#roster-table_filter').removeClass('roster-table_filter'); //Align search to right
			}
		});

		//Failed GET
		rosterPromise.fail(function() {
			$('#roster-table').append("<tr><td>Failed to load roster</td><td></td>/tr>").css('font-size','0.8em');
		});

	});
});