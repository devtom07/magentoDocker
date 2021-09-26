require(
    [
        'Magento_Ui/js/lib/validation/validator',
        'jquery',
        'mage/translate'
    ], function(validator, $){

        validator.addRule(
            'discount-reserved-validation',
            function(value, element) {
                if (isNaN(value)) {
                    return false;
                }
                return true;
            },
            $.mage.__("You must enter the number")
        );

        validator.addRule(
            'spending-point-validation',
            function(value, element) {
                if (isNaN(value)) {
                    return false;
                }
                return true;
            },
            $.mage.__("You must enter the number")
        );

        validator.addRule(
            'priority-validation',
            function(value, element) {
                if (isNaN(value)) {
                    return false;
                }
                return true;
            },
            $.mage.__("You must enter the number")
        );
    });
