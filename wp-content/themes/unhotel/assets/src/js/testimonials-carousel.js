/**
 * Testimonials Carousel Initialization
 * Handles Flickity initialization for testimonials carousel blocks
 */
(function($) {
	'use strict';
	
	function getItemsPerSlideForScreen(itemsPerSlide) {
		// Mobile first - always 1 on mobile (< 768px)
		if (window.innerWidth < 768) {
			return 1;
		}
		// Tablet - max 2 (768px - 1023px)
		if (window.innerWidth < 1024) {
			return Math.min(itemsPerSlide, 2);
		}
		// Desktop - max 3 (1024px - 1199px)
		if (window.innerWidth < 1200) {
			return Math.min(itemsPerSlide, 3);
		}
		// Wide desktop - use full setting (>= 1200px)
		return itemsPerSlide;
	}
	
	function initTestimonialsCarousel() {
		// Only initialize testimonials carousel, not regular carousel blocks
		$(".unhotel-testimonials-carousel").not(".unhotel-carousel").each(function() {
			var $carousel = $(this);
			var displayMode = $carousel.data("display-mode") || "slider";
			
			// Skip if grid mode - grid handles layout via CSS
			if (displayMode === "grid") {
				// Set equal heights for grid items
				setTimeout(function() {
					var $items = $carousel.find(".testimonial-item");
					var maxHeight = 0;
					$items.css("height", "auto");
					$items.each(function() {
						var height = $(this).outerHeight();
						if (height > maxHeight) {
							maxHeight = height;
						}
					});
					if (maxHeight > 0) {
						$items.css("height", maxHeight + "px");
					}
				}, 100);
				return;
			}
			
			var $track = $carousel.find(".testimonials-carousel-track");
			
			// Skip if already initialized
			if ($track.hasClass("flickity-enabled")) {
				return;
			}
			
			if ($track.length && typeof Flickity !== "undefined") {
				var itemsPerSlide = parseInt($carousel.data("items")) || 1;
				var showArrows = $carousel.data("arrows") === "true" || $carousel.data("arrows") === true;
				var showDots = $carousel.data("dots") === "true" || $carousel.data("dots") === true;
				
				// Get responsive items per slide (mobile first)
				var responsiveItems = getItemsPerSlideForScreen(itemsPerSlide);
				
				// For 4 items per slide, use equal heights
				var useEqualHeight = itemsPerSlide === 4 && responsiveItems === 4;
				
				try {
					var flkty = new Flickity($track[0], {
						cellAlign: "left",
						contain: false,
						pageDots: showDots,
						prevNextButtons: showArrows,
						groupCells: responsiveItems > 1 ? responsiveItems : false,
						wrapAround: true,
						adaptiveHeight: !useEqualHeight,
						imagesLoaded: true,
						arrowShape: "M 10,50 L 60,00 L 70,10 L 30,50 L 70,90 L 60,100 Z"
					});
					
					// Force Flickity to recalculate height after initialization
					if (flkty) {
						setTimeout(function() {
							if (useEqualHeight) {
								setEqualHeights($track, flkty);
							}
							flkty.resize();
							flkty.reposition();
						}, 200);
						
						// Also update on content change
						flkty.on("settle", function() {
							if (useEqualHeight) {
								setEqualHeights($track, flkty);
							}
							flkty.resize();
						});
						
						// Update on slide change
						flkty.on("change", function() {
							setTimeout(function() {
								if (useEqualHeight) {
									setEqualHeights($track, flkty);
								}
							}, 50);
						});
					}
					
					function setEqualHeights($track, flkty) {
						// Get all visible items in current slide
						var $visibleItems = $track.find(".testimonial-item");
						var maxHeight = 0;
						
						// First, remove any fixed heights to measure natural height
						$visibleItems.css("height", "auto");
						
						// Calculate max height
						$visibleItems.each(function() {
							var $item = $(this);
							// Temporarily remove truncation to measure full height
							var $wrapper = $item.find(".testimonial-text-wrapper");
							var wasTruncated = $wrapper.hasClass("truncated");
							$wrapper.removeClass("truncated");
							
							var height = $item[0].offsetHeight;
							if (height > maxHeight) {
								maxHeight = height;
							}
							
							// Restore truncation
							if (wasTruncated) {
								$wrapper.addClass("truncated");
							}
						});
						
						// Apply max height to all items
						if (maxHeight > 0) {
							$visibleItems.css("height", maxHeight + "px");
						}
					}
					
					// Update on window resize
					var resizeTimer;
					$(window).on("resize", function() {
						clearTimeout(resizeTimer);
						resizeTimer = setTimeout(function() {
							var newItems = getItemsPerSlideForScreen(itemsPerSlide);
							if (flkty && flkty.options.groupCells !== (newItems > 1 ? newItems : false)) {
								flkty.destroy();
								$track.removeClass("flickity-enabled");
								initTestimonialsCarousel();
							}
						}, 250);
					});
				} catch(e) {
					// Silently fail - carousel will not initialize if there's an error
					if (window.console && window.console.error) {
						console.error("Flickity initialization error:", e);
					}
				}
			}
		});
	}
	
	$(document).ready(function() {
		initTestimonialsCarousel();
	});
	
	// Also initialize after WordPress block rendering (for dynamic content)
	if (typeof wp !== "undefined" && wp.domReady) {
		wp.domReady(function() {
			initTestimonialsCarousel();
		});
	}
})(jQuery);

