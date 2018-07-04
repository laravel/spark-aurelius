/**
 * SparkForm helper class. Used to set common properties on all forms.
 */
window.SparkForm = function (data) {
    $.extend(this, data);

    /**
     * Create the form error helper instance.
     */
    Object.defineProperties(this, {
        errors: {
            enumerable: false,
            value: new SparkFormErrors,
            writable: true
        },
        busy: {
            enumerable: false,
            value: false,
            writable: true
        },
        successful: {
            enumerable: false,
            value: false,
            writable: true
        }
    });
};

/**
 * Start processing the form.
 */
window.SparkForm.startProcessing = function () {
    this.errors.forget();
    this.busy = true;
    this.successful = false;
};

/**
 * Finish processing the form.
 */
window.SparkForm.finishProcessing = function () {
    this.busy = false;
    this.successful = true;
};

/**
 * Reset the errors and other state for the form.
 */
window.SparkForm.resetStatus = function () {
    this.errors.forget();
    this.busy = false;
    this.successful = false;
};


/**
 * Set the errors on the form.
 */
window.SparkForm.setErrors = function (errors) {
    this.busy = false;
    this.errors.set(errors);
};
