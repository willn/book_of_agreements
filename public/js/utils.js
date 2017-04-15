
function hide_topics()
{
	var topics = document.getElementsByTagName('div');
	num_tops = topics.length;
	for(i=0; i<topics.length; i++) {
		//alert('i:' + i + ' class: ' + topics[i].className);
		if (topics[i].className == 'item_topic') {
			topics[i].className = 'item_topic invisible';
			topics[i].parentNode.onmouseover = function() { show_topic(this); }
			topics[i].parentNode.onmouseout = function() { hide_child(this); }
		}
	} 
}

function show_topic(parent_node) {
	children = parent_node.getElementsByTagName('div');
	children[0].className = 'item_topic';
}

function hide_child(child_node) {
	children = child_node.getElementsByTagName('div');
	children[0].className = 'item_topic invisible';
}

// this isn't quite as useful as I thought it would because the spacing /
// layout changes all the time, so going down the screen doesn't quite show
// each topic summary.
