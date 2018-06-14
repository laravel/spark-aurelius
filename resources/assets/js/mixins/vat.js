module.exports = {
    methods: {
        /**
         * Determine if the given country collects European VAT.
         */
        collectsVat(country) {
            return Spark.collectsEuropeanVat ? _.includes([
                'AT', 'BE', 'BG', 'CY', 'CZ',
                'DE', 'DK', 'EE', 'ES', 'FI',
                'FR', 'GB', 'GR', 'HR', 'HU',
                'IE', 'IT', 'LT', 'LU', 'LV',
                'MT', 'NL', 'PL', 'PT', 'RO',
                'SE', 'SI', 'SK', 'NO',
            ], country) : false;
        },


        /**
         * Refresh the tax rate using the given form input.
         */
        refreshTaxRate(form) {
            axios.post('/tax-rate', JSON.parse(JSON.stringify(form)))
                .then(response => {
                    this.taxRate = response.data.rate;
                });
        },


        /**
         * Get the tax amount for the selected plan.
         */
        taxAmount(plan) {
            return plan.price * (this.taxRate / 100);
        },


        /**
         * Get the total plan price including the applicable tax.
         */
        priceWithTax(plan) {
            return plan.price + this.taxAmount(plan);
        }
    }
};
