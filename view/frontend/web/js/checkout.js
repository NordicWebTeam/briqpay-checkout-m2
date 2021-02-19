/*jshint browser:true jquery:true*/
/*global alert*/
define([
    "jquery",
    'Magento_Ui/js/modal/alert',
    "jquery/ui",
    "mage/translate",
    "mage/mage",
    "mage/validation",
    "Magento_Customer/js/customer-data"
], function (jQuery, alert) {
    "use strict";
    jQuery.widget('mage.nwtbriqpayCheckout', {
        options: {
            shippingMethodFormSelector: '#shipping-method-form',
            shippingMethodLoaderSelector: '#shipping-method-loader',
            shippingMethodsListSelector: '#briqpay-checkout_shipping_method',
            shippingMethodCheckBoxHolder: '.briqpay-checkout-radio-row',
            getShippingMethodButton: '#shipping-method-button',
            newsletterFormSelector: '#briqpay-checkout-newsletter-form',
            couponFormSelector: '#discount-coupon-form',
            cartContainerSelector: '#details-table',
            waitLoadingContainer: '#review-please-wait',
            couponToggler: '#briqpay_coupon_toggle input',
            couponFormContainer: '#briqpay-checkout_coupon',
            commentFormSelector: '#briqpay-checkout-comment',
            commentTextAreaSelector: '#briqpay-checkout-comment .fieldset.comment',
            commentTextAreaToggler: '#briqpay-checkout-comment .briqpay-btn.show-more',
            commentTextAreaTogglerLess: '#briqpay-checkout-comment .briqpay-btn.show-less',
            ctrlkey: null,
            ctrlcookie: 'briqpay-checkoutCartCtrlKey',
            ctrkeyCheck: true,
            briqpayCountryChange: false,
            briqpayShippingChange: false,
            hasInitFlag: false,
            shippingAjaxInProgress: false,
            iframeOverlay: '#iframe-overlay'
        },
        _create: function () {
            this._checkIfCartWasUpdated();
            this._bindEvents();
            this.uiManipulate();
            this.toggleCouponContainer();
            this.toggleOrderCommentTextArea();
        },

        _checkIfCartWasUpdated: function () {
            var checkIfCartWasUpdated = setInterval((function () {
                var ctrlkey = jQuery.mage.cookies.get(this.options.ctrlcookie);
                if (ctrlkey && ctrlkey !== this.options.ctrlkey) {

                    // clear the interval
                    clearInterval(checkIfCartWasUpdated);

                    // msg popup, then reload!
                    jQuery(this.options.waitLoadingContainer).html('<span class="error">Cart was updated, please wait for the Checkout to reload...</span>').show();
                    window.location.reload();
                }
            }).bind(this), 1000);
        },

        _bindCartAjax: function () {
            var cart = this.options.cartContainerSelector;
            var inputs = jQuery(cart).find('.ajax-qty-change');
            var _this = this;
            jQuery.each(inputs, function (i, input) {
                var inputQty = jQuery(input);
                var data_submit_url = inputQty.data('cart-url-submit');
                var data_refresh_url = inputQty.data('cart-url-update');
                var data_remove_url = inputQty.data('cart-url-remove');
                var increment = inputQty.siblings('.input-number-increment');
                var decrement = inputQty.siblings('.input-number-decrement');
                var remove = inputQty.closest('tr').find('td.subtotal .remove-product');
                var prevVal = false;

                if (increment.data('binded')) return;
                if (decrement.data('binded')) return;
                if (remove.data('binded')) return;

                increment.data('binded', true);
                decrement.data('binded', true);
                remove.data('binded', true);

                increment.on('click', function () {
                    inputQty.val(parseInt(inputQty.val()) + 1);
                    if (typeof ajaxActionTriggerTimeout !== "undefined") {
                        clearTimeout(ajaxActionTriggerTimeout);
                    }
                    window.ajaxActionTriggerTimeout = setTimeout(function () {
                        inputQty.trigger('change');
                    }, 1000);
                });
                decrement.on('click', function () {
                    var v = parseInt(inputQty.val());
                    if (v < 2) return;
                    inputQty.val(v - 1);
                    if (typeof ajaxActionTriggerTimeout !== "undefined") {
                        clearTimeout(ajaxActionTriggerTimeout);
                    }
                    window.ajaxActionTriggerTimeout = setTimeout(function () {
                        inputQty.trigger('change');
                    }, 1000);
                });
                remove.on('click', function () {
                    var c = confirm(jQuery.mage.__('Are you sure you want to remove this?'));
                    if (c == true) {
                        var data = {
                            item_id: inputQty.data('cart-product-id'),
                            form_key: inputQty.data('cart-form-key')
                        };
                        jQuery.ajax({
                            type: "POST",
                            url: data_remove_url,
                            data: data,
                            beforeSend: function () {
                                _this._ajaxBeforeSend();
                            },
                            complete: function () {
                            },
                            success: function (data) {
                                if (!data.success) {
                                    if (data.error_message) {
                                        var errHtml = '<div class="message-error error message"><div>' + data.error_message + '</div></div>';
                                        jQuery('.page.messages').append(errHtml);
                                    }
                                }
                                _this._ajaxSubmit(data_refresh_url);
                            }
                        });
                    }
                });
                inputQty.on('keypress', function (e) {
                    if (e.keyCode == "1") {
                        inputQty.trigger('change');
                        return false;
                    };
                }).on('focus', function () {
                    prevVal = jQuery(inputQty).val();
                }).on('change', function () {
                    var data = {
                        item_id: inputQty.data('cart-product-id'),
                        form_key: inputQty.data('cart-form-key'),
                        item_qty: inputQty.val()
                    };
                    if (data.item_qty == 0) {
                        jQuery(inputQty).val(prevVal);
                        return false;
                    }
                    jQuery.ajax({
                        type: "POST",
                        url: data_submit_url,
                        data: data,
                        beforeSend: function () {
                            _this._ajaxBeforeSend();
                        },
                        complete: function () {
                            _briqpay.checkout.suspend();
                            _briqpay.checkout.resume()
                            _this._ajaxComplete()
                        },
                        success: function (data) {
                            if (!data.success) {
                                if (data.error_message) {
                                    var errHtml = '<div class="message-error error message"><div>' + data.error_message + '</div></div>';
                                    jQuery('.page.messages').append(errHtml);
                                }
                            }
                            _this._ajaxSubmit(data_refresh_url);
                        }
                    });
                });
            });
        },

        _bindEvents: function (block) {
            //$blocks = ['shipping_method','cart','coupon','messages', 'briqpay','newsletter'];

            block = block ? block : null;
            if (!block || block == 'shipping') {
                jQuery(this.options.shippingMethodLoaderSelector).on('submit', jQuery.proxy(this._loadShippingMethod, this));
            }
            if (!block || block == 'shipping_method') {
                jQuery(this.options.shippingMethodFormSelector).find('input[type=radio]').live('change', jQuery.proxy(this._changeShippingMethod, this));
            }
            if (!block || block == 'newsletter') {
                jQuery(this.options.newsletterFormSelector).find('input[type=checkbox]').on('change', jQuery.proxy(this._changeSubscriptionStatus, this));
            }
            if (!block || block == 'cart') {
                this._bindCartAjax();
            }
            if (!block || block == 'coupon') {
                jQuery(this.options.couponFormSelector).on('submit', jQuery.proxy(this._applyCoupon, this));
                this.checkValueOfInputs(jQuery(this.options.couponFormSelector));
                this.briqpayApiChanges();
            }
            if (!block || block == 'comment') {
                jQuery(this.options.commentFormSelector).on('submit', jQuery.proxy(this._saveComment, this));
                this.checkValueOfInputs(jQuery(this.options.commentFormSelector));
            }

            if (!block || block == 'briqpay') {
                this.briqpayApiChanges();
            }

            // Shipping methods
            jQuery(document).on('change', '.qty input', function() {
                if (window.shippingAjaxInProgress === true) {
                    return false;
                }

                window.shippingAjaxInProgress = true;
                setTimeout(function(){
                    jQuery.ajax({
                        context: '#shipping-method-form',
                        url:  'update/ReloadShippingMethods',
                        type: 'GET',
                        data: '',
                    }).done(function (data) {
                        jQuery('#shipping-method-form').html(data.output);
                        window.shippingAjaxInProgress = false;
                        return true;
                    });
                }, 2000);
            });
        },

        checkValueOfInputs: function (form) {
            var checkValue = function (elem) {
                if (jQuery(elem).val()) {
                    form.find('.primary').show();
                } else {
                    form.find('.primary').hide();
                }
            };
            var field = jQuery(form).find('.briqpay-checkout-show-on-focus').get(0);
            jQuery(field).on("keyup", function () {
                checkValue(this)
            });
            jQuery(field).on("change", function () {
                checkValue(this)
            });
        },


        /**
         * show ajax loader
         */
        _ajaxBeforeSend: function () {
            this.options.ctrkeyCheck = false;
            // this._hideBriqpayCheckout()
            jQuery(this.options.waitLoadingContainer).show();
        },

        /**
         * hide ajax loader
         */
        _ajaxComplete: function (dontHidePayment) {
            this._showBriqpayCheckout();
            jQuery(this.options.waitLoadingContainer).hide();
            this.briqpayApiChanges();
            this.toggleCouponContainer();
        },

        _showBriqpayCheckout: function () {
            _briqpay.checkout.resume()
        },

        _hideBriqpayCheckout: function () {
            _briqpay.checkout.suspend()
        },

        _changeShippingMethod: function () {
            this._ajaxFormSubmit(jQuery(this.options.shippingMethodFormSelector));
        },

        _loadShippingMethod: function () {
            this._ajaxFormSubmit(jQuery(this.options.shippingMethodLoaderSelector));
            return false;
        },

        _changeSubscriptionStatus: function () {
            this._ajaxFormSubmit(jQuery(this.options.newsletterFormSelector));
        },

        _applyCoupon: function () {
            this._ajaxFormSubmit(jQuery(this.options.couponFormSelector));
            return false;
        },

        _saveComment: function () {
            var form = jQuery(this.options.commentFormSelector);
            this._ajaxSubmit(form.prop('action'), form.serialize(), "post", false, function () {

                jQuery("#briqpay-submit").addClass('success-save');
            });
            return false;
        },

        _ajaxFormSubmit: function (form) {
            return this._ajaxSubmit(form.prop('action'), form.serialize());
        },
        /**
         * Attempt to ajax submit order
         */
        _ajaxSubmit: function (url, data, method, beforeBRIQPAYAjax, afterBRIQPAYAjax) {
            if (!method) method = 'post';
            var _this = this;
            if (this.options.shippingAjaxInProgress === true) {
                return false;
            }
            jQuery.ajax({
                url: url,
                type: method,
                context: this,
                data: data,
                dataType: 'json',
                beforeSend: function () {
                    _this.options.ctrkeyCheck = false;
                    _this.options.shippingAjaxInProgress = true;
                    _this._ajaxBeforeSend();
                    if (typeof beforeBRIQPAYAjax === 'function') {
                        beforeBRIQPAYAjax();
                    }
                },
                complete: function () {
                    _briqpay.checkout.suspend();
                    _briqpay.checkout.resume()
                    _this.options.shippingAjaxInProgress = false;
                    _this._ajaxComplete();
                },
                success: function (response) {
                    if (jQuery.type(response) === 'object' && !jQuery.isEmptyObject(response)) {

                        if (response.reload || response.redirect) {
                            this.loadWaiting = false; //prevent that resetLoadWaiting hiding loader
                            if (response.messages) {
                                //alert({content: response.messages});
                                jQuery(this.options.waitLoadingContainer).html('<span class="error">' + response.messages + ' Reloading...</span>');
                            } else {
                                jQuery(this.options.waitLoadingContainer).html('<span class="error">Reloading...</span>');
                            }

                            if (response.redirect) {
                                window.location.href = response.redirect;
                            } else {
                                window.location.reload();
                            }
                            return true;
                        } //end redirect


                        if (response.updates) {
                            var blocks = response.updates;
                            var div = null;

                            for (var block in blocks) {
                                if (blocks.hasOwnProperty(block)) {
                                    div = jQuery('#briqpay-checkout_' + block);
                                    if (div.size() > 0) {
                                        div.replaceWith(blocks[block]);
                                        this._bindEvents(block);
                                    }
                                    if (block === 'shipping_method') {
                                        jQuery(this.options.shippingMethodsListSelector).show();
                                    }
                                }
                            }
                        }

                        if (typeof afterBRIQPAYAjax === 'function') {
                            afterBRIQPAYAjax();
                        }

                        if (response.messages) {
                            alert({
                                content: response.messages
                            });
                        }

                    } else {
                        alert({
                            content: jQuery.mage.__('Sorry, something went wrong. Please try again (reload this page)')
                        });
                        // window.location.reload();
                    }
                },
                error: function () {
                    this.options.ctrkeyCheck = true;
                    alert({
                        content: jQuery.mage.__('Sorry, something went wrong. Please try again later.')
                    });
                    //window.location.reload();
//                     this._ajaxComplete();
                }
            });
        },

        briqpayApiChanges: function () {
            if (! window.briqpayCheckout) {
                return
            }
            window.briqpayCheckout.refreshForm();
        },

        /**
         * UI Stuff
         */
        getViewport: function () {
            var e = window, a = 'inner';
            if (!('innerWidth' in window)) {
                a = 'client';
                e = document.documentElement || document.body;
            }
            return {width: e[a + 'Width'], height: e[a + 'Height']};
        },
        sidebarFiddled: false,
        fiddleSidebar: function () {
            var t = this;
            if ((this.getViewport().width <= 960) && !this.sidebarFiddled) {
                jQuery('.mobile-collapse').each(function () {
                    jQuery(this).collapsible('deactivate');
                    t.sidebarFiddled = true;
                });
            }
        },
        uiManipulate: function () {
            var t = this;
            jQuery(window).resize(function () {
                t.fiddleSidebar();
            });
            jQuery(document).ready(function () {
                t.fiddleSidebar();
            });
        },

        toggleCouponContainer: function () {
            var target = this.options.couponFormContainer,
                toggler = this.options.couponToggler;

            jQuery(toggler).change(function () {
                if (this.checked)
                    jQuery(target).addClass('visible');
                else
                    jQuery(target).removeClass('visible');
            });
        },

        toggleOrderCommentTextArea: function () {
            var target = this.options.commentTextAreaSelector,
                toggler = this.options.commentTextAreaToggler,
                togglerLess = this.options.commentTextAreaTogglerLess;


            jQuery(toggler).on('click', function () {
                jQuery(target).slideDown(function () {
                    jQuery(toggler).hide();
                    jQuery(togglerLess).show();
                });
            });

            jQuery(togglerLess).on('click', function () {
                jQuery(target).slideUp(function () {
                    jQuery(togglerLess).hide();
                    jQuery(toggler).show();
                });
            });
        }
    });

    return jQuery.mage.nwtbriqpayCheckout;
});
