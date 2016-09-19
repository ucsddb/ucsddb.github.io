var viewport = window.matchMedia("(max-width: 450px)");


function openNav() 
{
	if(viewport.matches)//if mobile
	{
    	document.getElementById("sideNavId").style.width = "180px";
    }else
    {
    	document.getElementById("sideNavId").style.width = "250px";
    }
}


function closeNav() 
{
    document.getElementById("sideNavId").style.width = "0";
    document.getElementById("btnSideToggle").innerHTML = "☰";
}

$(document).ready(function()
{
	// Close the dropdown menu if the user clicks outside of it
	$(window).click(function(event){
	    if(parseInt(document.getElementById("sideNavId").style.width) > 0)
	    {
		    var clickedClass = $(event.target).attr('class');
	       if (!clickedClass.includes('sideDetect')) 
	       {
	       		closeNav();
	       }
	    } 
	});

	$(".sideNav_button").click(function()
	{
		$(this).text(function(i, text)
		{
			if(text === "☰")
			{
				openNav();
				return text = "✕";
			}else
			{
				closeNav();
				return text = "☰";
			}
    	})
	});
});

