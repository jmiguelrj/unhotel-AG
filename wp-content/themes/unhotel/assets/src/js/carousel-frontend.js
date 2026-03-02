/**
 * Carousel Block Frontend Initialization
 * Handles Flickity initialization for carousel blocks on the frontend
 */
(function() {
	'use strict';
	
	// Breakpoints for responsive design
	var BREAKPOINT_MOBILE = 768;  // Below 768px = mobile
	var BREAKPOINT_TABLET = 1024;  // 768px - 1024px = tablet, above = desktop
	
	/**
	 * Get the current items to show based on screen width
	 */
	function getCurrentItemsToShow(carouselEl) {
		var itemsToShowDesktop = parseInt(carouselEl.getAttribute('data-items-to-show')) || 3;
		var itemsToShowTablet = parseInt(carouselEl.getAttribute('data-items-to-show-tablet')) || 2;
		var itemsToShowMobile = parseInt(carouselEl.getAttribute('data-items-to-show-mobile')) || 1;
		
		var windowWidth = window.innerWidth || document.documentElement.clientWidth;
		
		if (windowWidth < BREAKPOINT_MOBILE) {
			return itemsToShowMobile;
		} else if (windowWidth < BREAKPOINT_TABLET) {
			return itemsToShowTablet;
		} else {
			return itemsToShowDesktop;
		}
	}
	
	function initCarousel(carouselEl, forceReinit) {
		if (!carouselEl) return;
		
		// If already initialized and not forcing reinit, check if we need to update
		if (carouselEl.classList.contains("flickity-enabled") && !forceReinit) {
			// Check if items to show needs to be updated
			var currentItemsToShow = getCurrentItemsToShow(carouselEl);
			var storedItemsToShow = carouselEl.getAttribute('data-current-items-to-show');
			
			if (storedItemsToShow && parseInt(storedItemsToShow) !== currentItemsToShow) {
				// Need to reinitialize with new items count
				forceReinit = true;
			} else {
				return;
			}
		}
		
		// Destroy existing instance if forcing reinit
		if (forceReinit && carouselEl.flickityInstance && !carouselEl.flickityInstance.isDestroyed) {
			carouselEl.flickityInstance.destroy();
			carouselEl.classList.remove("flickity-enabled");
		}
		
		// Wait for Flickity library
		if (typeof Flickity === "undefined") {
			setTimeout(function() {
				initCarousel(carouselEl, forceReinit);
			}, 100);
			return;
		}
		
		// Get settings from data attributes
		var carouselId = carouselEl.closest('.unhotel-carousel').id;
		var itemsToShow = getCurrentItemsToShow(carouselEl);
		var totalItems = parseInt(carouselEl.getAttribute('data-total-items')) || 0;
		var showArrows = carouselEl.getAttribute('data-show-arrows') === 'true';
		var showDots = carouselEl.getAttribute('data-show-dots') === 'true';
		var hasMoreItems = totalItems > itemsToShow;
		
		// Store current items to show for comparison on resize
		carouselEl.setAttribute('data-current-items-to-show', itemsToShow);
		
		// Determine if navigation should be shown
		// Only show if user enabled it AND there are more items than can fit on one slide
		var finalShowArrows = showArrows && hasMoreItems;
		var finalShowDots = showDots && hasMoreItems;
		
		var container = carouselEl.closest(".unhotel-carousel");
		if (container) {
			container.style.visibility = "visible";
			container.style.opacity = "1";
			container.style.display = "block";
		}
		
		try {
			var flkty = new Flickity(carouselEl, {
				cellAlign: "left",
				contain: true,
				pageDots: finalShowDots,
				prevNextButtons: finalShowArrows,
				groupCells: itemsToShow > 1 ? itemsToShow : false,
				wrapAround: false,
				adaptiveHeight: false,
				imagesLoaded: true,
				draggable: false,
				freeScroll: false,
				rightToLeft: false,
				arrowShape: "M 10,50 L 60,00 L 70,10 L 30,50 L 70,90 L 60,100 Z"
			});
			
			carouselEl.flickityInstance = flkty;
			
			// Immediately hide dots if they shouldn't be shown (Flickity might create them anyway)
			if (!finalShowDots) {
				setTimeout(function() {
					var dots = container.querySelectorAll(".flickity-page-dots");
					dots.forEach(function(dotContainer) {
						dotContainer.classList.remove("is-visible");
						dotContainer.style.display = "none";
						dotContainer.style.visibility = "hidden";
						dotContainer.style.opacity = "0";
						dotContainer.style.height = "0";
						dotContainer.style.overflow = "hidden";
					});
				}, 10);
			}
			
			// Immediately hide/show navigation based on settings
			function updateNavigationVisibility() {
				var buttons = container.querySelectorAll(".flickity-prev-next-button");
				var dots = container.querySelectorAll(".flickity-page-dots");
				
				// Handle arrows
				buttons.forEach(function(btn) {
					if (finalShowArrows) {
						btn.style.display = "block";
						btn.style.visibility = "visible";
						btn.style.opacity = "1";
					} else {
						btn.style.display = "none";
						btn.style.visibility = "hidden";
						btn.style.opacity = "0";
					}
				});
				
				// Handle dots - hide more forcefully
				dots.forEach(function(dotContainer) {
					if (finalShowDots) {
						dotContainer.classList.add("is-visible");
						dotContainer.style.display = "block";
						dotContainer.style.visibility = "visible";
						dotContainer.style.opacity = "1";
						dotContainer.style.height = "auto";
						dotContainer.style.overflow = "visible";
						container.style.paddingBottom = "4rem";
					} else {
						dotContainer.classList.remove("is-visible");
						dotContainer.style.display = "none";
						dotContainer.style.visibility = "hidden";
						dotContainer.style.opacity = "0";
						dotContainer.style.height = "0";
						dotContainer.style.overflow = "hidden";
						container.style.paddingBottom = "2rem";
					}
				});
			}
			
			// Wait for images to load and Flickity to fully initialize
			setTimeout(function() {
				// Re-get itemsToShow in case window was resized during initialization
				itemsToShow = getCurrentItemsToShow(carouselEl);
				hasMoreItems = totalItems > itemsToShow;
				finalShowArrows = showArrows && hasMoreItems;
				finalShowDots = showDots && hasMoreItems;
				
				// Update navigation visibility with correct values
				updateNavigationVisibility();
				
				if (container) {
					container.style.visibility = "visible";
					container.style.opacity = "1";
				}
				
				// Get container width
				var containerWidth = container ? container.offsetWidth : carouselEl.offsetWidth || 1200;
				var gap = 16; // 1rem in pixels
				var cellWidth = Math.floor((containerWidth - (gap * (itemsToShow - 1))) / itemsToShow);
				
				// Ensure all items have consistent height and width
				var items = carouselEl.querySelectorAll(".carousel-item");
				items.forEach(function(item) {
					// Check if it's an image item - set to 376x376
					if (item.classList.contains("carousel-image")) {
						item.style.height = "376px";
						item.style.width = "376px";
						item.style.minWidth = "376px";
						item.style.maxWidth = "376px";
					} else if (item.classList.contains("carousel-post")) {
						// Blog posts use calculated width but auto height
						item.style.height = "auto";
						item.style.width = cellWidth + "px";
						item.style.minWidth = cellWidth + "px";
						item.style.maxWidth = cellWidth + "px";
					} else {
						// Other items use calculated width
						item.style.height = "376px";
						item.style.width = cellWidth + "px";
						item.style.minWidth = cellWidth + "px";
						item.style.maxWidth = cellWidth + "px";
					}
					item.style.marginRight = gap + "px";
				});
				
				// Recalculate and position with proper containment
				if (flkty) {
					// Update cell sizes
					flkty.cells.forEach(function(cell) {
						if (cell.element) {
							cell.element.style.width = cellWidth + 'px';
							cell.element.style.minWidth = cellWidth + 'px';
							cell.element.style.maxWidth = cellWidth + 'px';
						}
					});
					
					// Force resize and reposition with containment
					flkty.resize();
					flkty.reposition();
					flkty.select(0, false, true);
					
					// Ensure viewport shows exactly itemsToShow items
					var viewport = carouselEl.querySelector('.flickity-viewport');
					if (viewport) {
						viewport.style.width = containerWidth + 'px';
						viewport.style.overflow = 'hidden';
					}
					
					// Final resize to ensure everything is correct
					setTimeout(function() {
						flkty.resize();
						flkty.reposition();
						flkty.select(0, false, true);
					}, 200);
				}
			}, 500);
			
			// Handle window resize
			var resizeTimer;
			var lastItemsToShow = itemsToShow;
			var resizeHandler = function() {
				clearTimeout(resizeTimer);
				resizeTimer = setTimeout(function() {
					var newItemsToShow = getCurrentItemsToShow(carouselEl);
					
					// If items to show changed, reinitialize the carousel
					if (newItemsToShow !== lastItemsToShow) {
						lastItemsToShow = newItemsToShow;
						initCarousel(carouselEl, true);
					} else if (flkty && !flkty.isDestroyed) {
						// Otherwise just resize
						flkty.resize();
						flkty.reposition();
					}
				}, 250);
			};
			window.addEventListener("resize", resizeHandler);
			
			// Store resize handler for cleanup if needed
			carouselEl._resizeHandler = resizeHandler;
		} catch(e) {
			// Silently fail - carousel will not initialize if there's an error
			if (window.console && window.console.error) {
				console.error("Flickity initialization error:", e);
			}
		}
	}
	
	function initAllCarousels() {
		var carousels = document.querySelectorAll('.unhotel-carousel.carousel-mode .carousel-track:not(.flickity-enabled)');
		carousels.forEach(function(carouselEl) {
			initCarousel(carouselEl);
		});
	}
	
	// Initialize when DOM is ready
	if (document.readyState === "loading") {
		document.addEventListener("DOMContentLoaded", function() {
			setTimeout(initAllCarousels, 300);
		});
	} else {
		setTimeout(initAllCarousels, 300);
	}
	
	// Fallback on window load
	window.addEventListener("load", function() {
		setTimeout(initAllCarousels, 500);
	});
})();

