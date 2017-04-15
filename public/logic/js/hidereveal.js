function showhide( show )
{
	var ps = document.getElementById( 'propselector' );
	var pchoice = ps.getAttribute( 'do' );
	var ms = document.getElementById( 'minselector' );
	var mchoice = ms.getAttribute( 'do' );

	alert( 'choices: ' + pchoice + mchoice );
	var items = document.getElementsByTagName( 'div' );
	var current;

	if ( pchoice == 'hidep' ) {
		if ( ps.firstChild.nodeType == 3 ) {
			ps.firstChild.nodeValue = 'show all';

			for ( var i=0; i < items.length; i++ )
			{
				current = items[i].getAttribute( "class" );
				if ( items[i].getAttribute( "class" ) == 'minutes' )
				{ items[i].style.display = 'none'; }
			}
		}
		ps.setAttribute( 'do', 'show' );
	}
	else if ( mchoice == 'hidem' ) {
		if ( ms.firstChild.nodeType == 3 ) {
			ms.firstChild.nodeValue = 'show all';

			for ( var i=0; i < items.length; i++ )
			{
				current = items[i].getAttribute( "class" );
				if ( items[i].getAttribute( "class" ) == 'agreement' )
				{ items[i].style.display = 'none'; }
			}
		}
		ms.setAttribute( 'do', 'show' );
	}
	else if (( pchoice == 'show' ) || ( mchoice == 'show' )) {
		for ( var i=0; i < items.length; i++ )
		{ items[i].style.display = 'block'; }
		ms.setAttribute( 'do', 'hide' );
		ps.setAttribute( 'do', 'hide' );
	}
	return false;
}

function prepareLinks( )
{
	if (!document.getElementsByTagName) return false;
	if (!document.getElementById( 'selectors' )) return false;

	document.getElementById( 'selectors' ).style.display = 'block';

	dinkdown = document.getElementById( 'selectors' );
	links = dinkdown.getElementsByTagName( 'a' );
	for( var i=0; i < links.length; i++ )
	{
		links[i].onclick = function( ) {
			return showhide( 1 );
		}
		links[i].onkeypress = links[i].onclick;
	}
}

function addLoadEvent(func) {
  var oldonload = window.onload;
  if (typeof window.onload != 'function') {
    window.onload = func;
  } else {
    window.onload = function() {
      oldonload();
      func();
    }
  }
}

addLoadEvent( prepareLinks );
