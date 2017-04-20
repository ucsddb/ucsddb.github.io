//---- JQuery -->
$(document).ready(function()
{
	/*handle the toggle mute/volume button at the bottom*/
	var video = document.getElementById("bVideo");
	video.volume = 0.10;
	console.log(video.volume);


	$(".mute_button").click(function()
	{
		if(video.muted == true)
		{
			video.muted = false;
			$('.mute_button').addClass('fa-volume-up');
			$('.mute_button').removeClass('fa-volume-off');


		}else //video audio is playing
		{
			video.muted = true;
			$('.mute_button').removeClass('fa-volume-up');
			$('.mute_button').addClass('fa-volume-off');
			
		}

		

	});
});

