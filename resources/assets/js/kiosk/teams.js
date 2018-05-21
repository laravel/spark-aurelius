module.exports = {
    props: ['user', 'team'],


    /**
     * The component's data.
     */
    data() {
        return {
            plans: [],

            teamSearchForm: new SparkForm({
                query: ''
            }),

            searching: false,
            noTeamSearchResults: false,
            teamSearchResults: [],

            showingTeamProfile: false
        };
    },


    /**
     * The component has been created by Vue.
     */
    created() {
        var self = this;

        this.$on('showTeamSearch', function() {
            self.navigateToSearch();
        });

        Bus.$on('sparkHashChanged', function(hash, parameters) {
            if (hash != Spark.teamsPrefix) {
                return true;
            }

            if (parameters && parameters.length > 0) {
                self.loadTeamProfile({ id: parameters[0] });
            } else {
                self.showTeamSearch();
            }

            return true;
        });
    },


    methods: {

        /**
         * Perform a search for the given query.
         */
        search() {
            this.searching = true;
            this.noTeamSearchResults = false;

            axios.post('/spark/kiosk/teams/search', this.teamSearchForm)
                .then(response => {
                    this.teamSearchResults = response.data;
                    this.noTeamSearchResults = this.teamSearchResults.length === 0;

                    this.searching = false;
                });
        },


        /**
         * Show the search results and update the browser history.
         */
        navigateToSearch() {
            history.pushState(null, null, '#teams');

            this.showTeamSearch();
        },


        /**
         * Show the search results.
         */
        showTeamSearch() {
            this.showingTeamProfile = false;

            Vue.nextTick(function() {
                $('#kiosk-teams-search').focus();
            });
        },


        /**
         * Show the team profile for the given team.
         */
        showTeamProfile(team) {
            history.pushState(null, null, '#/teams/' + team.id);

            this.loadTeamProfile(team);
        },


        /**
         * Load the team profile for the given team.
         */
        loadTeamProfile(team) {
            this.$emit('showTeamProfile', team.id);

            this.showingTeamProfile = true;
        },
    }
};
