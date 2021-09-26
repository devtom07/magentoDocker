require(
    [
        'Magento_Ui/js/lib/validation/validator',
        'jquery',
        'mage/translate'
    ], function(validator, $){

        validator.addRule(
            'money-spent-validation',
            function(value, element) {
                if (isNaN(value)) {
                    return false;
                }
                return true;
            },
            $.mage.__("You must enter the number")
        );

        validator.addRule(
            'earing-points-validation',
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
