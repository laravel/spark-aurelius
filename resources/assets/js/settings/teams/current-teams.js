module.exports = {
    props: ['user', 'teams'],


    /**
     * The component's data.
     */
    data() {
        return {
            leavingTeam: null,
            deletingTeam: null,

            leaveTeamForm: new SparkForm({}),
            deleteTeamForm: new SparkForm({})
        };
    },


    /**
     * Prepare the component.
     */
    mounted() {
        $('[data-toggle="tooltip"]').tooltip();
    },


    computed: {
        /**
         * Get the active subscription instance.
         */
        activeSubscription() {
            if ( ! this.$parent.billable) {
                return;
            }

            const subscription = _.find(
                this.$parent.billable.subscriptions,
                subscription => subscription.name == 'default'
            );

            if (typeof subscription !== 'undefined') {
                return subscription;
            }
        },


        /**
         * Determine if the current subscription is active.
         */
        subscriptionIsOnGracePeriod() {
            return this.activeSubscription &&
                this.activeSubscription.ends_at &&
                moment.utc().isBefore(moment.utc(this.activeSubscription.ends_at));
        },


        /**
         * Get the URL for leaving a team.
         */
        urlForLeaving() {
            return `/settings/${Spark.teamsPrefix}/${this.leavingTeam.id}/members/${this.user.id}`;
        }
    },


    methods: {
        /**
         * Approve leaving the given team.
         */
        approveLeavingTeam(team) {
            this.leavingTeam = team;

            $('#modal-leave-team').modal('show');
        },


        /**
         * Leave the given team.
         */
        leaveTeam() {
            Spark.delete(this.urlForLeaving, this.leaveTeamForm)
                .then(() => {
                    Bus.$emit('updateUser');
                    Bus.$emit('updateTeams');

                    $('#modal-leave-team').modal('hide');
                });
        },


        /**
         * Approve the deletion of the given team.
         */
        approveTeamDelete(team) {
            this.deletingTeam = team;

            $('#modal-delete-team').modal('show');
        },


        /**
         * Delete the given team.
         */
        deleteTeam() {
            Spark.delete(`/settings/${Spark.teamsPrefix}/${this.deletingTeam.id}`, this.deleteTeamForm)
                .then(() => {
                    Bus.$emit('updateUser');
                    Bus.$emit('updateTeams');

                    $('#modal-delete-team').modal('hide');
                });
        }
    }
};
