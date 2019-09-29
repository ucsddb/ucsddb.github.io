$(document).ready(function()
{
	$("#recruitment_dropdown").on("click", function()
	{
		$("#recruitment_dropdown_text").toggleClass("invisible");
		$("#right_arrow").toggleClass("down_arrow_right");
		$("#right_arrow").toggleClass("up_arrow_right");
		$("#left_arrow").toggleClass("down_arrow_left");
		$("#left_arrow").toggleClass("up_arrow_left");

		/*
			google maps centering doesn't work if element is hidden.
			must call centering method once element is revealed.
		 */
		if (!$("#recruitment_dropdown_text").hasClass("invisible")){
			var labelMarker = 0; 
			function center() {
				var bounds = new google.maps.LatLngBounds();

				function addMarker(feature, index) 
				{
					var marker = new google.maps.Marker({
						position: feature.position,
						map: map
					});
					bounds.extend(feature.position);

				}/*end of add marker function*/
				var features = 
				[
					{
						position: new google.maps.LatLng(32.878686, -117.236255)//town square
					}, {
						position: new google.maps.LatLng(32.879511, -117.231568),//Warren Field
					}, {
						position: new google.maps.LatLng(32.885531, -117.240635),//Rimac Annex
					}, {
						position: new google.maps.LatLng(32.877204, -117.241835),//Natatorium
					}, {
						position: new google.maps.LatLng(32.879747, -117.239736),//Peterson Loop
					}, {
						position: new google.maps.LatLng(32.8811438,-117.2349294), // Warren Mall
					}/*, {
						position: new google.maps.LatLng(32.858705, -117.255538),//la Jolla Shores
					}, {
						position: new google.maps.LatLng(32.777212, -117.215159)//missionbay
					}*/
				];
				for (var i = 0, feature; feature = features[i]; i++) 
				{
					addMarker(feature, i);
				}
				map.fitBounds(bounds);
			}
			center();
		}
	});

	// no clicking on mobile device
	if (window.matchMedia('(max-device-width: 768px)').matches){
		$("#recruitment_dropdown").parent().toggleClass("horz_block flex_center");
		$("#recruitment_dropdown").html("<h3>2019 Recruitment Information</h3>");
		$("#recruitment_dropdown").css("pointer-events", "none");
		$("#recruitment_dropdown").off();
		$("#recruitment_dropdown_text").toggleClass("invisible");
		$("#recruitment_dropdown_text").children().first().toggleClass("recruitment-info-mobile");
	}

	/*
		when page loads, must close message by clicking x.
		then message is invisble and side button reappears.
	 */
	$(".close").click(function()
	{
		$(".message_recruitment_over_cover").css("display", "none");
	});

	/*
		switch between nav bar and side nav depending on if bar is visible
	*/
	let navigation_menu = document.querySelector(".navigation_links");
	let observer;
	let options = {
					root: null,
					rootMargin: "0px",
					threshold: [0, 1.0]
				};
	observer = new IntersectionObserver(handleIntersect, options);
	observer.observe(navigation_menu);

	function handleIntersect(entries, observer){
		entries.forEach(function(entry) {
			if (entry.intersectionRatio > 0.5) {
				$("#sideNavId").css("display", "none");
				$("#btnSideToggle").css("display", "none");
				if ($("#sideNavId").css("width") != "0px"){
					$(".sideNav_button").trigger("click");
				}
			} else {
				$("#sideNavId").css("display", "block");
				$("#btnSideToggle").css("display", "block");
			}
		  });
	}


	/* announcements section
	
	let lastSeen = new Date(localStorage.getItem("timeSeen"));
	let currentTime = new Date();
	let seenMessage = localStorage.getItem("recruitmentAnnouncement");

	if ((currentTime - lastSeen) > (1000 * 60 * 60 * 24 * 7)){
		seenMessage = false;
	}

	if(!seenMessage){
		localStorage.setItem("recruitmentAnnouncement", true);
		$(".message_recruitment_over_cover").css("display", "block");

		let timeSeen = new Date();
		localStorage.setItem("timeSeen", timeSeen.toUTCString());
	}*/

	/*
	$(window).click(function(event){
		if ($(event.target).is("#message_recruitment_over")){
			$(".message_recruitment_over_cover").css("display", "none");
		}
	});*/

});