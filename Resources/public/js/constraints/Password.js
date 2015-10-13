function SymfonyComponentSecurityCoreValidatorConstraintsUserPassword() {
    this.message = 'Old password is invalid';
    this.fields = [];
    this.errorPath = null;

    this.validate = function (value, element) {
        var self = this,
            elementNode = document.getElementById(element.id),
            route = elementNode.getAttribute('ajax-validate-route');

        if (!route) {
            return [];
        }

        FpJsFormValidator.ajax.sendRequest(
            route,
            {
                message: this.message,
                data: { value: elementNode.value }
            },
            function (response) {
                response = JSON.parse(response);
                var errors = [];
                if (false === response) {
                    errors.push(self.message);
                }
                FpJsFormValidator.customize(element.domNode, 'showErrors', {
                    errors: errors,
                    sourceId: 'user-password-' + self.uniqueId
                });
            }
        );

        return [];
    };
}