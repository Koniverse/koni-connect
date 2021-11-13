function web3vnWacoWalletSelectionModalHelper($, polkadotIconUrl) {
    const MODAL_ID = 'web3vn-waco-modal-select-wallet';
    const sModal = `#${MODAL_ID}`;
    const modalFooterClass = 'waco-m-footer';
    const sModalFooter = `${sModal} .${modalFooterClass}`;
    const accountListClass = 'waco-m-account-wallet';
    const sAccountList = `${sModal} .${accountListClass}`;
    const confirmBtnClass = 'waco-m-confirm-btn';
    const sConfirmBtn = `${sModal} .${confirmBtnClass}`;
    const confirmSpinnerClass = 'waco-m-confirm-spinner';
    const sConfirmSpinner = `${sModal} .${confirmSpinnerClass}`;

    async function selectWallet() {
        const extensions = await pdot_dappex.web3Enable('web3vn-waco');

        if (!extensions.length) {
            showRequestAccess();
            return
        }

        if (!pdot_dappex.isWeb3Injected) {
            showPickerNone();
        } else {
            pdot_dappex.web3Accounts().then(async (accounts) => {
                showPicker(accounts);
            });
        }
    }

    function toggleModalFooter(toggle) {
        if (toggle) {
            $(sModalFooter).removeClass('waco-h-hidden');
        } else {
            $(sModalFooter).addClass('waco-h-hidden');
        }
    }

    function showRequestAccess() {
        $(sAccountList).html(`
                            <p style="margin-top: 0">Please allow this site to access polkadot{.js} extension. If you have not installed the extension, please click the button below:</p>
                            		<a class="btn btn-primary justify-content-between align-items-center w-100" target="_blank" href="https://polkadot.js.org/extension/">Install Polkadot{.js} Extension
                            		${polkadotIconUrl ? `<img style="width:32px; margin-left: 10px" src="${polkadotIconUrl}" alt="Polkadot Icon">`: ''}
                            </a>`);
        toggleModalFooter(false);
        $(sModal).modal('show');
    }

    function showPickerNone() {
        $(sAccountList).html(`
		<a class="btn btn-primary justify-content-between align-items-center w-100" target="_blank" href="https://polkadot.js.org/extension/">Install Polkadot{.js} Extension
            ${polkadotIconUrl ? `<img style="width:32px; margin-left: 10px" src="${polkadotIconUrl}" alt="Polkadot Icon">`: ''}
		</a>
		`);
        toggleModalFooter(false);
        $(sModal).modal('show');
    }

    function showPicker(accounts) {
        $(sConfirmBtn).prop('disabled', true);
        let selector = $(sAccountList);
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
        $(sModal).modal('show');
    }

    function toggleModalFooterLoading(toggle) {
        if (toggle) {
            $(sConfirmBtn).prop('disabled', true);
            $(sConfirmSpinner).show();
        } else {
            $(sConfirmSpinner).hide();
        }
    }

    function registerOnSelectAddress() {
        $(sAccountList).on('change', 'input[name=select_wallet]', function () {
            $(sConfirmBtn).prop('disabled', false);
        });
    }

    return {
        MODAL_ID,
        S_MODAL: sModal,
        S_CONFIRM_BTN: sConfirmBtn,
        selectWallet,
        toggleModalFooter,
        showRequestAccess,
        showPickerNone,
        showPicker,
        toggleModalFooterLoading,
        registerOnSelectAddress
    }
}

function web3vnWacoBnUtil() {
    const {BN_ONE, BN_TEN, BN_TWO, BN_ZERO, formatBalance, isBn, isUndefined} = pdot_util;
    const BN = pdot_bn;

    const BitLengthOption = {
        CHAIN_SPEC: 128,
        NORMAL_NUMBERS: 32
    }

    const DEFAULT_BITLENGTH = BitLengthOption.NORMAL_NUMBERS;
    const DEFAULT_DECIMALS = 12;
    const DEFAULT_AUX = ['Aux1', 'Aux2', 'Aux3', 'Aux4', 'Aux5', 'Aux6', 'Aux7', 'Aux8', 'Aux9'];

    function reformat(value, isReadOnly, siDecimals) {
        if (!value) {
            return [];
        }

        const decimals = isUndefined(siDecimals)
            ? formatBalance.getDefaults().decimals
            : siDecimals;
        const si = isReadOnly
            ? formatBalance.calcSi(value.toString(), decimals)
            : formatBalance.findSi('-');

        return [
            formatBalance(value, {decimals, forceUnit: si.value, withSi: false}).replace(/,/g, isReadOnly ? ',' : ''),
            si
        ];
    }

    function getGlobalMaxValue(bitLength) {
        return BN_TWO.pow(new BN(bitLength || DEFAULT_BITLENGTH)).isub(BN_ONE);
    }

    function getSiOptions(symbol, decimals) {
        return formatBalance.getOptions(decimals).map(({power, text, value}) => ({
            text: power === 0
                ? symbol
                : text,
            value
        }));
    }

    function getSiPowers(si, decimals) {
        if (!si) {
            return [BN_ZERO, 0, 0];
        }

        const basePower = isUndefined(decimals)
            ? formatBalance.getDefaults().decimals
            : decimals;

        return [new BN(basePower + si.power), basePower, si.power];
    }

    function isValidNumber(bn, bitLength, isZeroable, maxValue) {
        if (
            // cannot be negative
            bn.lt(BN_ZERO) ||
            // cannot be > than allowed max
            bn.gt(getGlobalMaxValue(bitLength)) ||
            // check if 0 and it should be a value
            (!isZeroable && bn.isZero()) ||
            // check that the bitlengths fit
            (bn.bitLength() > (bitLength || DEFAULT_BITLENGTH)) ||
            // cannot be > max (if specified)
            (maxValue && maxValue.gtn(0) && bn.gt(maxValue))
        ) {
            return false;
        }

        return true;
    }

    function inputToBn(api, input, si, decimals) {
        const [siPower, basePower, siUnitPower] = getSiPowers(si, decimals);

        // eslint-disable-next-line @typescript-eslint/prefer-regexp-exec
        const isDecimalValue = input.match(/^(\d+)\.(\d+)$/);

        let result;

        if (isDecimalValue) {
            if (siUnitPower - isDecimalValue[2].length < -basePower) {
                result = new BN(-1);
            }

            const div = new BN(input.replace(/\.\d*$/, ''));
            const modString = input.replace(/^\d+\./, '').substr(0, api.registry.chainDecimals[0]);
            const mod = new BN(modString);

            result = div
                .mul(BN_TEN.pow(siPower))
                .add(mod.mul(BN_TEN.pow(new BN(basePower + siUnitPower - modString.length))));
        } else {
            result = new BN(input.replace(/[^\d]/g, ''))
                .mul(BN_TEN.pow(siPower));
        }

        return result;
    }

    function getValues(api, value = BN_ZERO, si, decimals) {
        return isBn(value)
            ? getValuesFromBn(value, si, decimals)
            : getValuesFromString(api, value, si, decimals);
    }

    function getValuesFromString(api, value, si, decimals) {
        const valueBn = inputToBn(api, value ? value.toString() : value, si, decimals);

        return [
            value,
            valueBn
        ];
    }

    function getValuesFromBn(valueBn, si, _decimals) {
        const decimals = isUndefined(_decimals)
            ? formatBalance.getDefaults().decimals
            : _decimals;
        const value = si
            ? valueBn.div(BN_TEN.pow(new BN(decimals + si.power))).toString()
            : valueBn.toString();

        return [
            value,
            valueBn
        ];
    }

    function getRegex(isDecimal) {
        const decimal = '.';

        return new RegExp(
            isDecimal
                ? `^(0|[1-9]\\d*)(\\${decimal}\\d*)?$`
                : '^(0|[1-9]\\d*)$'
        );
    }

    return {
        DEFAULT_AUX,
        DEFAULT_DECIMALS,
        BitLengthOption,
        reformat,
        getGlobalMaxValue,
        getSiOptions,
        getSiPowers,
        isValidNumber,
        inputToBn,
        getValues,
        getValuesFromString,
        getValuesFromBn,
        getRegex
    };
}