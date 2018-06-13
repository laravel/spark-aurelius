module.exports = {
    props: ['user', 'plans'],


    /**
     * The component's data.
     */
    data() {
        return {
            loading: false,
            team: null,
            revenue: 0
        };
    },

    /**
     * The component has been created by Vue.
     */
    created() {
        var self = this;

        this.$parent.$on('showTeamProfile', function(id) {
            self.getTeamProfile(id);
        });
    },


    /**
     * Prepare the component.
     */
    mounted() {
        Mousetrap.bind('esc', e => this.showTeamSearch());
    },


    methods: {
        /**
         * Get the profile user.
         */
        getTeamProfile(id) {
            this.loading = true;

            axios.get('/spark/kiosk/teams/' + id + '/profile')
                .then(response => {
                    this.team = response.data.team;
                    this.revenue = response.data.revenue;

                    this.loading = false;
                });
        },


        /**
         * Get the plan the user is actively subscribed to.
         */
        activePlan(billable) {
            if (this.activeSubscription(billable)) {
                var activeSubscription = this.activeSubscription(billable);

                return _.find(this.plans, (plan) => {
                    return plan.id == activeSubscription.provider_plan;
                });
            }
        },


        /**
         * Get the active, valid subscription for the user.
         */
        activeSubscription(billable) {
            var subscription = this.subscription(billable);

            if (!subscription || (subscription.ends_at && moment.utc().isAfter(moment.utc(subscription.ends_at)))) {
                return;
            }

            return subscription;
        },


        /**
         * Get the active subscription instance.
         */
        subscription(billable) {
            if (!billable) {
                return;
            }

            const subscription = _.find(
                billable.subscriptions,
                subscription => subscription.name == 'default');

            if (typeof subscription !== 'undefined') {
                return subscription;
            }
        },


        /**
         * Show the search results and hide the user profile.
         */
        showTeamSearch() {
            this.$parent.$emit('showTeamSearch');

            this.team = null;
        },

        /**
         * Show the users profile
         */
        showUserProfile(user) {
            window.location = '#/users/' + user.id;
        },

		/**
		 * Show the edit team view
		 */
		showEditTeam(team) {
			window.location = '/team/' + team.id + '/edit';
		}
    }
};
