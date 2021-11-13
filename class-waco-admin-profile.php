<?php
/**
 * Add extra profile fields for users in admin
 *
 * @author   WooThemes
 * @category Admin
 * @package  WooCommerce\Admin
 * @version  2.4.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!class_exists('Web3vn_WaCo_Admin_Profile', false)) :

    /**
     * WC_Admin_Profile Class.
     */
    class Web3vn_WaCo_Admin_Profile
    {

        /**
         * Hook in tabs.
         */
        public function __construct()
        {
            add_action('show_user_profile', array($this, 'add_customer_meta_fields'));
            add_action('edit_user_profile', array($this, 'add_customer_meta_fields'));
        }

        public static function printSelectWalletModal() {
            ?>
            <div class="modal fade waco-modal" id="web3vn-waco-modal-select-wallet" tabindex="-1" aria-labelledby="modelLang"
                 aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Select your wallet</h5>
                            <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"><i
                                        class="fa fa-times" aria-hidden="true" style="font-size: 1.5rem;"></i></button>
                        </div>
                        <div class="modal-body">
                            <div class="waco-m-account-wallet"></div>
                        </div>
                        <div class="modal-footer waco-h-hidden waco-m-footer">
                            <div style="display: flex; justify-content: flex-end; align-items: center;">
                                <i class="fa fa-spinner fa-spin waco-m-confirm-spinner" aria-hidden="true"
                                   style="margin-right: 10px;
        font-size: 18px; display: none"></i>
                                <button type="button" class="btn btn-primary waco-btn waco-m-confirm-btn"
                                        disabled>Confirm
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php
        }

        /**
         * Show Address Fields on edit user pages.
         *
         * @param WP_User $user
         */
        public function add_customer_meta_fields($user)
        {
            ?>
            <h2>
                Wallet Connect
            </h2>

            <div style="">
                <a id="web3vn-waco-connectW"
                   style="max-width: 300px"
                   class="btn btn-primary justify-content-between align-items-center w-100 waco-h-hidden"><span>Connect Wallet</span></a>

            </div>

            <?php self::printSelectWalletModal() ?>

            <table class="form-table hidden" id="web3vn-waco-connection-info">
                <tr>
                    <th>
                        <label>Name</label>
                    </th>
                    <td>
                        <input type="text" name="web3vn_waco_name" id="web3vn_waco_name"
                               readonly
                               class="regular-text"/>
                    </td>
                </tr>

                <tr>
                    <th>
                        <label>Address</label>
                    </th>
                    <td>
                        <input type="text" name="web3vn_waco_address" id="web3vn_waco_address"
                               readonly
                               class="large-text"/>
                    </td>
                </tr>
            </table>

            <script>
                (function ($) {
                    const polkadotScriptUrl = `<?php echo WEB3VN_WACO_URL . '/assets/js/polkadot.js?ver=' . WEB3VN_WACO_VERSION ?>`;
                    const polkadotIconUrl = `<?php echo WEB3VN_WACO_URL . '/assets/images/polkadotjs-icon.svg' ?>`;

                    let WalletSelectionModalHelperFunc;

                    function confirmSelectedWallet() {
                        const $selected = $('input[name=select_wallet]:checked', WalletSelectionModalHelperFunc.S_MODAL);
                        if ($selected.length) {
                            WalletSelectionModalHelperFunc.toggleModalFooterLoading(true);

                            const name = $selected.data('name');
                            const address = $selected.val();

                            $.ajax({
                                url: web3vnWacoApi.root + 'web3vn-waco/profile',
                                method: 'POST',
                                beforeSend: function (xhr) {
                                    xhr.setRequestHeader('X-WP-Nonce', web3vnWacoApi.nonce);
                                },
                                data: {
                                    name,
                                    address
                                }
                            }).done(function (response) {
                                if (!response.code) {
                                    updateWacoValue({
                                        name,
                                        address
                                    });

                                    $(WalletSelectionModalHelperFunc.S_MODAL).modal('hide');
                                }
                            }).always(function () {
                                WalletSelectionModalHelperFunc.toggleModalFooterLoading(false);
                            });
                        } else {
                            console.log('Please select account first');
                        }
                    }

                    function updateWacoValue(data) {
                        $('#web3vn_waco_name').val(data.name);
                        $('#web3vn_waco_address').val(data.address);
                        $('#web3vn-waco-connection-info').removeClass('hidden');
                    }

                    function checkAccountThenShowInfo() {
                        $.ajax({
                            url: web3vnWacoApi.root + 'web3vn-waco/profile',
                            method: 'GET',
                            beforeSend: function (xhr) {
                                xhr.setRequestHeader('X-WP-Nonce', web3vnWacoApi.nonce);
                            }
                        }).done(function (response) {
                            if (!response.code && response.data) {
                                updateWacoValue(response.data);
                            }
                        }).fail(function (response) {
                            console.log("waco - error :", response);
                        })
                    }

                    $(document).ready(function () {
                        const $selectWalletBtn = $('#web3vn-waco-connectW');

                        $.ajax({
                            url: polkadotScriptUrl,
                            dataType: 'script',
                            cache: true, // or get new, fresh copy on every page load
                            success: function() {
                                WalletSelectionModalHelperFunc = web3vnWacoWalletSelectionModalHelper($, polkadotIconUrl);

                                $selectWalletBtn.on('click', WalletSelectionModalHelperFunc.selectWallet);
                                $(WalletSelectionModalHelperFunc.S_CONFIRM_BTN).on('click', confirmSelectedWallet);
                                WalletSelectionModalHelperFunc.registerOnSelectAddress();

                                $selectWalletBtn.removeClass('waco-h-hidden');
                            }
                        })

                        checkAccountThenShowInfo();
                    });
                })(jQuery);
            </script>
            <?php
        }
    }
endif;

return new Web3vn_WaCo_Admin_Profile();
