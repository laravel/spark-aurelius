module.exports = {
    methods: {
        /**
         * Get the current discount for the given billable entity.
         */
        getCurrentDiscountForBillable(type, billable) {
            if (type === 'user') {
                return this.getCurrentDiscountForUser(billable);
            } else {
                return this.getCurrentDiscountForTeam(billable);
            }
        },

        /**
         * Get the current discount for the user.
         */
        getCurrentDiscountForUser(user) {
            this.currentDiscount = null;

            this.loadingCurrentDiscount = true;

            axios.get(`/coupon/user/${user.id}`)
                .then(response => {
                    if (response.status == 200) {
                        this.currentDiscount = response.data;
                    }

                    this.loadingCurrentDiscount = false;
                });
        },


        /**
         * Get the current discount for the team.
         */
        getCurrentDiscountForTeam(team) {
            this.currentDiscount = null;

            this.loadingCurrentDiscount = true;

            axios.get(`/settings/${Spark.teamsPrefix}/coupon/${team.id}`)
                .then(response => {
                    if (response.status == 200) {
                        this.currentDiscount = response.data;
                    }

                    this.loadingCurrentDiscount = false;
                });
        },


        /**
         * Get the formatted discount amount for the given discount.
         */
        formattedDiscount(discount) {
            if ( ! discount) {
                return
            }

            if (discount.percent_off) {
                return `${discount.percent_off}%`;
            } else {
                return Vue.filter('currency')(
                    this.calculateAmountOff(discount.amount_off)
                );
            }
        },


        /**
         * Calculate the amount off for the given discount amount.
         */
        calculateAmountOff(amount) {
            return amount / 100;
        }
    }
};
