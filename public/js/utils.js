/*
 * Toggle the visibility of the "show X previous versions" link, using vanilla js.
 */
var toggleHideShow = function (event) {
	if (!event.target.classList.contains('toggle')) {
		return;
	}

	// Prevent default link behavior
	event.preventDefault();

	// the "show" link
	var reveal = document.querySelector('#versions_reveal');
	reveal.classList.toggle('is-visible');

	// the "hide" link
	var diffs = document.querySelector('#versions');
	diffs.classList.toggle('is-visible');
};


document.addEventListener("DOMContentLoaded", function() {
	var versionsBlock = document.querySelector('#versions_block');
	var listener = versionsBlock.addEventListener('click', toggleHideShow, false);
});

document.addEventListener("click", function() {
	console.log('hey');
	if (!event.target.matches('#print_document')) {
		return;
	}
	event.preventDefault();
	
	console.log('hey');
	window.print();
});
