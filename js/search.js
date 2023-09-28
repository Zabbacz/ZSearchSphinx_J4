function get_text(event)
{
	let string = event.textContent;

	//fetch api

	fetch("index.php?option=com_ajax&module=zsearchsphinx&format=raw", {

		method:"POST",

		body: JSON.stringify({
			search_query : string
		}),

		headers : {
			"Content-type" : "application/json; charset=UTF-8"
		}
	}).then(function(response){
		return response.json();

	}).then(function(responseData){
	
		document.getElementsByName('search_box')[0].value = string;
	
		document.getElementById('search_result').innerHTML = '';

	});

	

}

function load_data(query)
{
	if(query.length > 2)
	{
		let form_data = new FormData();

		form_data.append('query', query);

		let ajax_request = new XMLHttpRequest();

		ajax_request.open('POST', 'index.php?option=com_ajax&module=zsearchsphinx&format=raw', true);
		
        
        ajax_request.send(form_data);

		ajax_request.onreadystatechange = function()
		{
			if(ajax_request.readyState == 4 && ajax_request.status == 200)
			{
				let response = JSON.parse(ajax_request.responseText);

				let html = '<div class="list-group">';

				if(response.length > 0)
				{
					for(let count = 0; count < response.length; count++)
					{
						const newLocal = '<a href="#" class="list-group-item list-group-item-action" onclick="get_text(this)">';
						html += newLocal+response[count].product_name+'</a>';
					}
				}
				else
				{
					html += '<a href="#" class="list-group-item list-group-item-action disabled">No Data Found</a>';
				}

				html += '</div>';

				document.getElementById('search_result').innerHTML = html;
			}
		}
	}
	else
	{
		document.getElementById('search_result').innerHTML = '';
	}
}