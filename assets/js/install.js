jQuery(document).ready(function ($) {

	// Determine steps
	var step = $('.srp-step').attr('rel');

	// Checks steps
	if (1 == step) {

		// Install the cache
		srp_install_wizard(1);

	} else if (2 == step) {

		// Link the posts
		$('#srp-link-now').click(function () {
			srp_install_wizard(2);
		});

	}

	function srp_install_wizard(step) {

		this.step = step;
		this.total_posts = 0;
		this.ppr = null;
		this.req_nr = 0;
		this.action = null;

		this.do_request = function () {
			var instance = this;
			$.post(ajaxurl, {
				'action'    : this.action,
				'rel_amount': $('#srp_related_posts_amount').val()
			}, function (response) {

				// What next?
				if ('more' == response) {
					// Increase the request nr
					instance.req_nr++;

					// Do Progressbar
					instance.do_progressbar();

					// Do request
					instance.do_request();

				} else {
					// Done
					instance.done();
				}

			});
		};

		this.done = function () {

			// Update progressbar
			$('#progressbar').progressbar({value: 100});

			// Redirect to next step
			//window.location = $('#srp_admin_url').val() + '?page=srp_install&step=' + ( this.step + 1 );
		};

		this.do_progressbar = function () {
			$('#progressbar').progressbar({value: ((this.req_nr * this.ppr) / this.total_posts) * 100});
		};

		this.init = function () {

			// Setup the progressbar
			$('#progressbar').progressbar({value: false});

			// Get the total posts
			this.total_posts = $('#srp_total_posts').val();

			// Set the correct action
			switch (this.step) {
				case 1:
					this.ppr = 200;
					this.action = 'srp_install_save_words';
					break;
				case 2:
					this.ppr = 50;
					this.action = 'srp_install_link_posts';
					break;
			}

			// Do the first request
			this.do_request();
		};

		this.init();

	}

});