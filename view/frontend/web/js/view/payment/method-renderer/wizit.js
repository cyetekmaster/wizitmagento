/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/

define(
    [
        'jquery',
        'Magento_Ui/js/modal/modal',
        'Magento_Checkout/js/view/payment/default',
        'Magento_Checkout/js/model/url-builder',
        'mage/url',
        'Magento_Checkout/js/model/quote',
    ],
    function ($, modal, Component, urlBuilder,
        url,
        quote) {
        'use strict';

        if(window.checkoutConfig.payment.wizit.default_country != 'AU' ){
            return Component.extend({
                defaults: {
                    template: 'Wizit_Wizit/payment/outzoneform',
                },
            });
        }

        return Component.extend({
            defaults: {
                template: 'Wizit_Wizit/payment/form',
            },


            redirectAfterPlaceOrder: false,

            getCode: function () {
                return 'wizit';
            },

            getLogoUrl: function () {
                return window.checkoutConfig.payment.wizit.wizitLogoUrl;
            },
            getUrlc: function () {
                return window.checkoutConfig.payment.wizit.urls;
            },

            getTitle : function(){
                return window.checkoutConfig.payment.wizit.wizitTitle;
            },

            totalamount: function () {
                var price = quote.getTotals()().base_grand_total;
                return price.toFixed(2);
            },

            installment: function () {
                var price = quote.getTotals()().base_grand_total;
                var formatedprice = price / 4;
                return formatedprice.toFixed(2);
            },

            getStoreCurrency: function () {
                return window.checkoutConfig.payment.wizit.getStoreCurrency;

            },

            afterPlaceOrder: function () {
                window.location.replace(url.build('wizit/index'));
            },

            context: function () {
                return this;
            },

            isActive: function () {
                return true;
            },

            popimage: function () {
                return window.checkoutConfig.payment.wizit.popimage;
            },

            showPopup: function () {
                var couponcodepopup = {
                    type: 'popup',
                    responsive: true,
                    innerScroll: true,
                    buttons: false,
                    modalClass: "wz-custom-modal",
                    clickableOverlay: true,
                    heightStyle: "content"
                };
                modal(couponcodepopup, $('#popup-modal'));
                $(".wz-custom-modal header.modal-header").appendTo("div#popup-modal");
                $('#popup-modal').modal('openModal');
            }
        });
    }
);