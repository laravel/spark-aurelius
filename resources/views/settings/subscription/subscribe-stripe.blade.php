<spark-subscribe-stripe :user="user" :team="team"
                        :plans="plans" :billable-type="billableType" inline-template>

    <div>
        <!-- Common Subscribe Form Contents -->
    @include('spark::settings.subscription.subscribe-common')

    <!-- Billing Information -->
        <div class="card card-default" v-show="selectedPlan">
            <div class="card-header">{{__('Billing Information')}}</div>

            <div class="card-body">
                <!-- Generic 500 Level Error Message / Stripe Threw Exception -->
                <div class="alert alert-danger" v-if="form.errors.has('form')">
                    {{__('We had trouble validating your card. It\'s possible your card provider is preventing us from charging the card. Please contact your card provider or customer support.')}}
                </div>

                <form role="form">
                    <!-- Payment Method -->
                    <div class="form-group row" v-if="hasPaymentMethod()">
                        <label for="use_existing_payment_method" class="col-md-4 col-form-label text-md-right">{{__('Payment Method')}}</label>

                        <div class="col-md-6">
                            <select name="use_existing_payment_method" v-model="form.use_existing_payment_method" id="use_existing_payment_method" class="form-control">
                                <option value="1">{{__('Use existing payment method')}}</option>
                                <option value="0">{{__('Use a different method')}}</option>
                            </select>
                        </div>
                    </div>

                    <!-- Cardholder's Name -->
                    <div class="form-group row" v-show="form.use_existing_payment_method != '1'">
                        <label for="name" class="col-md-4 col-form-label text-md-right">{{__('Cardholder\'s Name')}}</label>

                        <div class="col-md-6">
                            <input type="text" class="form-control" name="name" v-model="cardForm.name">
                        </div>
                    </div>

                    <!-- Card Details -->
                    <div class="form-group row" v-show="form.use_existing_payment_method != '1'">
                        <label for="name" class="col-md-4 col-form-label text-md-right">{{__('Card')}}</label>

                        <div class="col-md-6">
                            <div id="subscription-card-element"></div>
                            <span class="invalid-feedback" v-show="cardForm.errors.has('card')">
                                @{{ cardForm.errors.get('card') }}
                            </span>
                        </div>
                    </div>

                    <!-- Billing Address Fields -->
                    @if (Spark::collectsBillingAddress())
                        @include('spark::settings.subscription.subscribe-address')
                    @endif

                    <!-- ZIP Code -->
                    <div class="form-group row" v-if=" ! spark.collectsBillingAddress">
                        <label for="number" class="col-md-4 col-form-label text-md-right">{{__('ZIP / Postal Code')}}</label>

                        <div class="col-md-6">
                            <input type="text" class="form-control" name="zip" v-model="form.zip">
                        </div>
                    </div>

                    <!-- Coupon -->
                    <div class="form-group row">
                        <label class="col-md-4 col-form-label text-md-right">{{__('Coupon')}}</label>

                        <div class="col-md-6">
                            <input type="text" class="form-control" v-model="form.coupon" :class="{'is-invalid': form.errors.has('coupon')}">

                        <span class="invalid-feedback" v-show="form.errors.has('coupon')">
                            @{{ form.errors.get('coupon') }}
                        </span>
                        </div>
                    </div>

                    <!-- Tax / Price Information -->
                    <div class="form-group row" v-if="spark.collectsEuropeanVat && countryCollectsVat && selectedPlan">
                        <label class="col-md-4 col-form-label text-md-right">&nbsp;</label>

                        <div class="col-md-6">
                            <div class="alert alert-info" style="margin: 0;">
                                <strong>{{__('Tax')}}:</strong> @{{ taxAmount(selectedPlan) | currency }}
                                <br><br>
                                <strong>{{__('Total Price Including Tax')}}:</strong>
                                @{{ priceWithTax(selectedPlan) | currency }}
                                @{{ selectedPlan.type == 'user' && spark.chargesUsersPerSeat ? '/ '+ spark.seatName : '' }}
                                @{{ selectedPlan.type == 'user' && spark.chargesUsersPerTeam ? '/ '+ __('teams.team') : '' }}
                                @{{ selectedPlan.type == 'team' && spark.chargesTeamsPerSeat ? '/ '+ spark.teamSeatName : '' }}
                                @{{ selectedPlan.type == 'team' && spark.chargesTeamsPerMember ? '/ '+ __('teams.member') : '' }}
                                / @{{ __(selectedPlan.interval) | capitalize }}
                            </div>
                        </div>
                    </div>

                    <!-- Subscribe Button -->
                    <div class="form-group row mb-0">
                        <div class="col-md-6 offset-md-4">
                            <button type="submit" class="btn btn-primary" @click.prevent="subscribe" :disabled="form.busy">
                            <span v-if="form.busy">
                                <i class="fa fa-btn fa-spinner fa-spin"></i> {{__('Subscribing')}}
                            </span>

                            <span v-else>
                                {{__('Subscribe')}}
                            </span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</spark-subscribe-stripe>
