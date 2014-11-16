$(function() {
	/* Source: http://apeatling.com/2014/building-smooth-sliding-mobile-menu/ */
	$('#toggleMenu').on('touchstart click', function(e) {
		e.preventDefault();

		var 	$body = $('body'),
				$page = $('#content'),
				$menu = $('#sidebarContainer'),
				transitionEnd = 'transitionend webkitTransitionEnd otransitionend MSTransitionEnd';
		
		$body.addClass('animating');
		
		if ($body.hasClass('menu-visible')) {
			$body.addClass('right');
			$menu.addClass('sidebarInvisible');
		}
		else {
			$body.addClass('left');
			$menu.removeClass('sidebarInvisible');
		}

		$page.on(transitionEnd, function() {
			$body.removeClass('animating left right').toggleClass('menu-visible');
			$page.off(transitionEnd);
			$("#sidebar").slimscroll({ height: 'auto' });
		});
	});
});