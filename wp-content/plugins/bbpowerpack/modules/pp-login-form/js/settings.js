(function($){

	FLBuilder.registerModuleHelper('pp-login-form', {
		init: function() {
            // Toggle reCAPTCHA display
            $('input[name=enable_recaptcha]').on('change', $.proxy(this._toggleReCaptcha, this));
            $('select[name=recaptcha_validate_type]').on('change', $.proxy(this._toggleReCaptcha, this));
            $('input[name=recaptcha_theme]').on('change', $.proxy(this._toggleReCaptcha, this));

            // Render reCAPTCHA after layout rendered via AJAX
            if (window.onLoadPPReCaptcha) {
                $(FLBuilder._contentClass).on('fl-builder.layout-rendered', onLoadPPReCaptcha);
            }
		},

        /**
		 * Custom preview method for reCAPTCHA settings
		 *
		 * @param  object event  The event type of where this method been called
		 * @since 2.8.0
		 */
        _toggleReCaptcha: function (event) {
            var form = $('.fl-builder-settings'),
                nodeId = form.attr('data-node'),
                enabled = form.find('input[name=enable_recaptcha]'),
                captchaKey = pp_recaptcha.site_key,
                captType = form.find('select[name=recaptcha_validate_type]').val(),
                theme = form.find('input[name=recaptcha_theme]').val(),
                reCaptcha = $('.fl-node-' + nodeId).find('.pp-grecaptcha'),
                reCaptchaId = nodeId + '-pp-grecaptcha',
                target = typeof event !== 'undefined' ? $(event.currentTarget) : null,
                typeEvent = target != null && typeof target.attr('name') !== typeof undefined && target.attr('name') === 'recaptcha_validate_type',
                themeEvent = target != null && typeof target.attr('name') !== typeof undefined && target.attr('name') === 'recaptcha_theme',
                scriptTag = $('<script>'),
				isRender = false;
				
			if ( 'invisible_v3' === captType ) {
				captType = 'invisible';
				captchaKey = pp_recaptcha.v3_site_key;
			}

            // Add library if not exists
            if (0 === $('script#g-recaptcha-api').length) {
                scriptTag
                    .attr('src', 'https://www.google.com/recaptcha/api.js?onload=onLoadPPReCaptcha&render=explicit')
                    .attr('type', 'text/javascript')
                    .attr('id', 'g-recaptcha-api')
                    .attr('async', 'async')
                    .attr('defer', 'defer')
                    .appendTo('body');
            }

            if ('yes' === enabled.val() && captchaKey.length) {

                // reCAPTCHA is not yet exists
                if (0 === reCaptcha.length) {
                    isRender = true;
                }
                // If reCAPTCHA element exists, then reset reCAPTCHA if existing key does not matched with the input value
                else if ((typeEvent || themeEvent) && (reCaptcha.data('sitekey') != captchaKey || reCaptcha.data('validate') != captType || reCaptcha.data('theme') != theme)
                ) {
                    reCaptcha.parent().remove();
                    isRender = true;
                }
                else {
                    reCaptcha.parent().show();
                }

                if (isRender) {
                    this._renderReCaptcha(nodeId, reCaptchaId, captchaKey, captType, theme);
                }
            }
            else if ('yes' === enabled.val() && captchaKey.length === 0 && reCaptcha.length > 0) {
                reCaptcha.parent().remove();
            }
            else if ('yes' !== enabled.val() && reCaptcha.length > 0) {
                reCaptcha.parent().hide();
            }
        },

		/**
		 * Render Google reCAPTCHA
		 *
		 * @param  string nodeId  		The current node ID
		 * @param  string reCaptchaId  	The element ID to render reCAPTCHA
		 * @param  string reCaptchaKey  The reCAPTCHA Key
		 * @param  string reCaptType  	Checkbox or invisible
		 * @param  string theme         Light or dark
		 * @since 2.8.0
		 */
        _renderReCaptcha: function (nodeId, reCaptchaId, reCaptchaKey, reCaptType, theme) {
            var captchaField = $('<div class="pp-login-form-field pp-field-group pp-field-type-recaptcha">'),
                captchaElement = $('<div id="' + reCaptchaId + '" class="pp-grecaptcha">'),
                widgetID;

            captchaElement.attr('data-sitekey', reCaptchaKey);
            captchaElement.attr('data-validate', reCaptType);
            captchaElement.attr('data-theme', theme);

            // Append recaptcha element to an appended element
            captchaField
                .html(captchaElement)
                .insertBefore($('.fl-node-' + nodeId).find('.pp-login-form .pp-login-form-fields > .pp-field-type-submit'));

            widgetID = grecaptcha.render(reCaptchaId, {
                sitekey: reCaptchaKey,
                size: reCaptType,
                theme: theme
            });
            captchaElement.attr('data-widgetid', widgetID);
        }
	});

})(jQuery);
