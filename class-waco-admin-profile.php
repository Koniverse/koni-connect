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
                   class="btn btn-primary justify-content-between align-items-center w-100"><span>Connect Wallet</span></a>

            </div>

            <div class="modal fade" id="web3vn-waco-modal-lg-wallet" tabindex="-1" aria-labelledby="modelLang"
                 aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel">Select your wallet</h5>
                            <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"><i
                                        class="fa fa-times" aria-hidden="true" style="font-size: 1.5rem;"></i></button>
                        </div>
                        <div class="modal-body">
                            <div id="web3vn-waco-account-wallet"></div>
                        </div>
                        <div class="modal-footer hidden" id="web3vn-waco-modal-footer">
                            <div style="display: flex; justify-content: flex-end; align-items: center;">
                                <i class="fa fa-spinner fa-spin" aria-hidden="true" id="web3vn-waco-confirm-spinner"
                                   style="margin-right: 10px;
        font-size: 18px; display: none"></i>
                                <button type="button" class="btn btn-primary waco-btn" id="web3vn-waco-confirm-selected"
                                        disabled>Confirm
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

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
                    async function selectWallet() {
                        const extensions = await dappex.web3Enable('web3vn-waco');

                        if (!extensions.length) {
                            showRequestAccess();
                            return
                        }

                        if (!dappex.isWeb3Injected) {
                            showPickerNone();
                        } else {
                            dappex.web3Accounts().then(async (accounts) => {
                                showPicker(accounts);
                            });
                        }
                    }

                    function confirmSelectedWallet() {
                        const $selected = $('input[name=select_wallet]:checked', '#web3vn-waco-account-wallet');
                        if ($selected.length) {
                            toggleModalFooterLoading(true);

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

                                    $('#web3vn-waco-modal-lg-wallet').modal('hide');
                                }
                            }).always(function () {
                                toggleModalFooterLoading(false);
                            });
                        } else {
                            console.log('Please select account first');
                        }
                    }

                    function toggleModalFooter(toggle) {
                        if (toggle) {
                            $('#web3vn-waco-modal-footer').removeClass('hidden');
                        } else {
                            $('#web3vn-waco-modal-footer').addClass('hidden');
                        }
                    }

                    function toggleModalFooterLoading(toggle) {
                        if (toggle) {
                            $('#web3vn-waco-confirm-selected').prop('disabled', true);
                            $('#web3vn-waco-confirm-spinner').show();
                        } else {
                            $('#web3vn-waco-confirm-spinner').hide();
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

                    function showRequestAccess() {
                        $('#web3vn-waco-account-wallet').html(`
                            <p style="margin-top: 0">Please allow this site to access polkadot{.js} extension. If you have not installed the extension, please click the button below:</p>
                            		<a class="btn btn-primary justify-content-between align-items-center w-100" target="_blank" href="https://polkadot.js.org/extension/">Install Polkadot{.js} Extension
                            <img style="width:32px; margin-left: 10px" src="<?php echo WEB3VN_WACO_URL . '/assets/images/polkadotjs-icon.svg' ?>">
                            </a>`);
                        toggleModalFooter(false);
                        $('#web3vn-waco-modal-lg-wallet').modal('show');
                    }

                    function showPickerNone() {
                        $('#web3vn-waco-account-wallet').html(`
		<a class="btn btn-primary justify-content-between align-items-center w-100" target="_blank" href="https://polkadot.js.org/extension/">Install Polkadot{.js} Extension
		<img style="width:32px; margin-left: 10px" src="<?php echo WEB3VN_WACO_URL . '/assets/images/polkadotjs-icon.svg' ?>">
		</a>
		`);
                        toggleModalFooter(false);
                        $('#web3vn-waco-modal-lg-wallet').modal('show');
                    }

                    function showPicker(accounts) {
                        $('#web3vn-waco-confirm-selected').prop('disabled', true);
                        let selector = $('#web3vn-waco-account-wallet');
                        selector.html('');
                        let str = ``;
                        if (accounts.length) {
                            for (let i = 0; i < accounts.length; i++) {
                                let optn = accounts[i].address;
                                let name = accounts[i].meta.name;
                                str += '<p><label><input type="radio" name="select_wallet" data-name="' + name + '" value="' + optn + '" /> <b>' + name + ':</b> ' + optn + '</label></p>';
                            }
                            selector.html(str);
                            toggleModalFooter(true);
                        } else {
                            str = `<p style="margin-top: 0">There is no account to show.</p>`
                            selector.html(str);
                        }
                        $('#web3vn-waco-modal-lg-wallet').modal('show');
                    }

                    $(document).ready(function () {
                        checkAccountThenShowInfo();
                        document.querySelector("#web3vn-waco-connectW").addEventListener('click', selectWallet);
                        document.querySelector("#web3vn-waco-confirm-selected").addEventListener('click', confirmSelectedWallet);

                        $('#web3vn-waco-account-wallet').on('change', 'input[name=select_wallet]', function () {
                            $('#web3vn-waco-confirm-selected').prop('disabled', false);
                        })
                    });
                })(jQuery);
            </script>
            <?php
        }
    }
endif;

return new Web3vn_WaCo_Admin_Profile();
