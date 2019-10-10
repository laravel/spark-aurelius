module.exports = {
    props: ['user', 'team', 'billableType'],

    /**
     * Load mixins for the component.
     */
    mixins: [
        require('./../../mixins/stripe')
    ],

    /**
     * The component's data.
     */
    data() {
        return {
            cardElement: null,
            
            form: new SparkForm({
                stripe_payment_method: '',
                address: '',
                address_line_2: '',
                city: '',
                state: '',
                zip: '',
                country: 'US'
            }),

            cardForm: new SparkForm({
                name: '',
            })
        };
    },


    /**
     * Prepare the component.
     */
    mounted() {
        this.cardElement = this.createCardElement('#payment-card-element');

        this.initializeBillingAddress();
    },


    methods: {
        /**
         * Initialize the billing address form for the billable entity.
         */
        initializeBillingAddress() {
            if (! Spark.collectsBillingAddress) {
                return;
            }

            this.form.address = this.billable.billing_address;
            this.form.address_line_2 = this.billable.billing_address_line_2;
            this.form.city = this.billable.billing_city;
            this.form.state = this.billable.billing_state;
            this.form.zip = this.billable.billing_zip;
            this.form.country = this.billable.billing_country || 'US';
        },


        /**
         * Update the billable's card information.
         */
        update(e) {
            this.form.busy = true;
            this.form.errors.forget();
            this.form.successful = false;
            this.cardForm.errors.forget();

            const payload = {
                name: this.cardForm.name,
                address: {
                    line1: this.form.address || '',
                    line2: this.form.address_line_2 || '',
                    city: this.form.city || '',
                    state: this.form.state || '',
                    postal_code: this.form.zip || '',
                    country: this.form.country || '',
                }
            };

            this.generateToken(secret => {
                this.stripe.handleCardSetup(secret, this.cardElement, {
                    payment_method_data: {
                        billing_details: payload
                    }
                }).then(response => {
                    if (response.error) {
                        this.cardForm.errors.set({card: [
                                response.error.message
                            ]});

                        this.form.busy = false;
                    } else {
                        this.sendUpdateToServer(response.setupIntent.payment_method);
                    }
                });
            });
        },


        /**
         * Send the credit card update information to the server.
         */
        sendUpdateToServer(paymentMethod) {
            this.form.stripe_payment_method = paymentMethod;

            Spark.put(this.urlForUpdate, this.form)
                .then((response) => {
                    Bus.$emit('updateUser');
                    Bus.$emit('updateTeam');

                    this.cardForm.name = '';
                    this.cardForm.number = '';
                    this.cardForm.cvc = '';
                    this.cardForm.month = '';
                    this.cardForm.year = '';

                    if ( ! Spark.collectsBillingAddress) {
                        this.form.zip = '';
                    }
                });
        }
    },


    computed: {
        /**
         * Get the billable entity's "billable" name.
         */
        billableName() {
            return this.billingUser ? this.user.name : this.team.owner.name;
        },


        /**
         * Get the URL for the payment method update.
         */
        urlForUpdate() {
            return this.billingUser
                            ? '/settings/payment-method'
                            : `/settings/${Spark.teamsPrefix}/${this.team.id}/payment-method`;
        },


        /**
         * Get the proper brand icon for the customer's credit card.
         */
        cardIcon() {
            if (! this.billable.card_brand) {
                return 'fa-cc-stripe';
            }

            switch (this.billable.card_brand) {
                case 'amex':
                    return 'fa-cc-amex';
                case 'diners':
                    return 'fa-cc-diners-club';
                case 'discover':
                    return 'fa-cc-discover';
                case 'jcb':
                    return 'fa-cc-jcb';
                case 'mastercard':
                    return 'fa-cc-mastercard';
                case 'visa':
                    return 'fa-cc-visa';
                default:
                    return 'fa-cc-stripe';
            }
        },


        /**
         * Get the placeholder for the billable entity's credit card.
         */
        placeholder() {
            if (this.billable.card_last_four) {
                return `************${this.billable.card_last_four}`;
            }

            return '';
        }
    }
};
