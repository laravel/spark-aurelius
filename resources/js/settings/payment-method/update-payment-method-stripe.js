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
                stripe_token: '',
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
        update() {
            this.form.busy = true;
            this.form.errors.forget();
            this.form.successful = false;
            this.cardForm.errors.forget();

            // Here we will build out the payload to send to Stripe to obtain a card token so
            // we can create the actual subscription. We will build out this data that has
            // this credit card number, CVC, etc. and exchange it for a secure token ID.
            const payload = {
                name: this.cardForm.name,
                address_line1: this.form.address || '',
                address_line2: this.form.address_line_2 || '',
                address_city: this.form.city || '',
                address_state: this.form.state || '',
                address_zip: this.form.zip || '',
                address_country: this.form.country || '',
            };

            // Once we have the Stripe payload we'll send it off to Stripe and obtain a token
            // which we will send to the server to update this payment method. If there is
            // an error we will display that back out to the user for their information.
            this.stripe.createToken(this.cardElement, payload).then(response => {
                if (response.error) {
                    this.cardForm.errors.set({card: [
                        response.error.message
                    ]});

                    this.form.busy = false;
                } else {
                    this.sendUpdateToServer(response.token.id);
                }
            });
        },


        /**
         * Send the credit card update information to the server.
         */
        sendUpdateToServer(token) {
            this.form.stripe_token = token;

            Spark.put(this.urlForUpdate, this.form)
                .then(() => {
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
                case 'American Express':
                    return 'fa-cc-amex';
                case 'Diners Club':
                    return 'fa-cc-diners-club';
                case 'Discover':
                    return 'fa-cc-discover';
                case 'JCB':
                    return 'fa-cc-jcb';
                case 'MasterCard':
                    return 'fa-cc-mastercard';
                case 'Visa':
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
