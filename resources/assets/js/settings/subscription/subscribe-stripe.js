module.exports = {
    props: ['user', 'team', 'plans', 'billableType'],

    /**
     * Load mixins for the component.
     */
    mixins: [
        require('./../../mixins/plans'),
        require('./../../mixins/subscriptions'),
        require('./../../mixins/vat'),
        require('./../../mixins/stripe')
    ],


    /**
     * The component's data.
     */
    data() {
        return {
            taxRate: 0,

            cardElement: null,

            form: new SparkForm({
                use_existing_payment_method: this.hasPaymentMethod() ? '1' : '0',
                stripe_payment_method: '',
                plan: '',
                coupon: null,
                address: '',
                address_line_2: '',
                city: '',
                state: '',
                zip: '',
                country: 'US',
                vat_id: ''
            }),

            cardForm: new SparkForm({
                name: ''
            })
        };
    },


    watch: {
        /**
         * Watch for changes on the entire billing address.
         */
        'currentBillingAddress': function (value) {
            if ( ! Spark.collectsEuropeanVat) {
                return;
            }

            this.refreshTaxRate(this.form);
        }
    },


    /**
     * Prepare the component.
     */
    mounted() {
        this.cardElement = this.createCardElement('#subscription-card-element');

        this.initializeBillingAddress();

        if (this.onlyHasYearlyPaidPlans) {
            this.showYearlyPlans();
        }
    },


    methods: {
        /**
         * Initialize the billing address form for the billable entity.
         */
        initializeBillingAddress() {
            this.form.address = this.billable.billing_address;
            this.form.address_line_2 = this.billable.billing_address_line_2;
            this.form.city = this.billable.billing_city;
            this.form.state = this.billable.billing_state;
            this.form.zip = this.billable.billing_zip;
            this.form.country = this.billable.billing_country || 'US';
            this.form.vat_id = this.billable.vat_id;
        },


        /**
         * Mark the given plan as selected.
         */
        selectPlan(plan) {
            this.selectedPlan = plan;

            this.form.plan = this.selectedPlan.id;
        },


        /**
         * Subscribe to the specified plan.
         */
        subscribe() {
            this.cardForm.errors.forget();

            this.form.startProcessing();

            if (this.form.use_existing_payment_method == '1') {
                return this.createSubscription();
            }

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
                        this.createSubscription(response.setupIntent.payment_method);
                    }
                });
            });
        },


        /*
         * After obtaining the Stripe token, create subscription on the Spark server.
         */
        createSubscription(token) {
            this.form.stripe_payment_method = token;

            axios.post(this.urlForNewSubscription, this.form)
                .then(response => {
                    Bus.$emit('updateUser');
                    Bus.$emit('updateTeam');
                }).catch(errors => {
                    if (errors.response.status == 400) {
                        window.location = '/' + Spark.cashierPath + '/payment/' + errors.response.data.paymentId + '?redirect=' + this.urlForPlanRedirect;
                    } else {
                        this.form.setErrors(errors.response.data.errors);
                    }
                });
        },


        /**
         * Determine if the user has subscribed to the given plan before.
         */
        hasSubscribed(plan) {
            return !!_.filter(this.billable.subscriptions, {provider_plan: plan.id}).length
        },


        /**
         * Show the plan details for the given plan.
         *
         * We'll ask the parent subscription component to display it.
         */
        showPlanDetails(plan) {
            this.$parent.$emit('showPlanDetails', plan);
        },


        /**
         * Determine if the user/team has a payment method defined.
         */
        hasPaymentMethod() {
            return this.team ? this.team.card_last_four : this.user.card_last_four;
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
         * Determine if the selected country collects European VAT.
         */
        countryCollectsVat()  {
            return this.collectsVat(this.form.country);
        },


        /**
         * Get the URL for subscribing to a plan.
         */
        urlForNewSubscription() {
            return this.billingUser
                            ? '/settings/subscription'
                            : `/settings/${Spark.teamsPrefix}/${this.team.id}/subscription`;
        },


        /**
         * Get the URL to redirect to after confirmation.
         */
        urlForPlanRedirect() {
            return this.billingUser
                            ? `/settings%23/subscription`
                            : `/settings/${Spark.teamsPrefix}/${this.team.id}%23/subscription`;
        },


        /**
         * Get the current billing address from the subscribe form.
         *
         * This used primarily for watching.
         */
        currentBillingAddress() {
            return this.form.address +
                   this.form.address_line_2 +
                   this.form.city +
                   this.form.state +
                   this.form.zip +
                   this.form.country +
                   this.form.vat_id;
        }
    }
};
