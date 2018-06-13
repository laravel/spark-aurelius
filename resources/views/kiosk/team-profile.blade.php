<spark-kiosk-team-profile :team="team" inline-template>
    <div>
        <!-- Loading Indicator -->
        <div class="row" v-if="loading">
            <div class="col-md-12">
                <div class="card card-default">
                    <div class="card-body">
                        <i class="fa fa-btn fa-spinner fa-spin"></i> {{__('Loading')}}
                    </div>
                </div>
            </div>
        </div>

        <!-- User Profile -->
        <div v-if=" ! loading && team">
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-default">
                        <div class="card-header">
                            <div class="btn-table-align">
                                <i class="fa fa-btn fa-times" style="cursor: pointer;" @click="showTeamSearch"></i>
                                @{{ team.name }}
                            </div>
                        </div>

                        <div class="card-body">
                            <div class="row">
                                <!-- Profile Photo -->
                                <div class="col-md-3 text-center">
                                    <img :src="team.photo_url" class="spark-profile-photo-xl">
                                </div>

                                <div class="col-md-9">
                                    <!-- Name -->
                                    <p>
                                        <strong>{{__('Name')}}:</strong> @{{ team.name }}
                                    </p>

                                    <!-- Joined Date -->
                                    <p>
                                        <strong>{{__('Joined')}}:</strong> @{{ team.created_at | datetime }}
                                    </p>

                                    <!-- User -->
                                    <p>
                                        <strong>{{__('Users')}}:</strong> @{{ team.users.length }}
                                    </p>

                                    <!-- Subscription -->
                                    <p>
                                        <strong>{{__('Subscription')}}:</strong>

                                        <span v-if="activePlan(team)">
                                            <a :href="customerUrlOnBillingProvider(team)" target="_blank">
                                                @{{ activePlan(team).name }} (@{{ activePlan(team).interval | capitalize }})
                                            </a>
                                        </span>

                                        <span v-else>
                                            {{__('None')}}
                                        </span>
                                    </p>

                                    <!-- Total Revenue -->
                                    <p>
                                        <strong>{{__('Total Revenue')}}:</strong> @{{ revenue | currency(spark.currencySymbol) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Users -->
            <div class="row" v-if="team.users.length > 0">
                <div class="col-md-12">
                    <div class="card card-default">
                        <div class="card-header">
                            {{__('Team Users')}}
                        </div>

                        <div class="table-responsive">
                            <table class="table table-valign-middle mb-0">
                                <thead>
                                    <th></th>
                                    <th>{{__('Name')}}</th>
                                    <th>{{__('E-Mail Address')}}</th>
                                    <th>{{__('Role')}}</th>
                                    <th>{{__('Subscription')}}</th>
                                    <th></th>
                                </thead>

                                <tbody>
                                    <tr v-for="user in team.users">
                                        <!-- Profile Photo -->
                                        <td>
                                            <img :src="user.photo_url" class="spark-profile-photo">
                                        </td>

                                        <!-- Name -->
                                        <td>
                                            <div class="btn-table-align">
                                                @{{ user.name }}
                                            </div>
                                        </td>

                                        <!-- E-Mail Address -->
                                        <td>
                                            <div class="btn-table-align">
                                                @{{ user.email }}
                                            </div>
                                        </td>

                                        <!-- Role -->
                                        <td>
                                            <div class="btn-table-align">
                                                @{{ user.pivot.role | capitalize }}
                                            </div>
                                        </td>

                                        <!-- Subscription -->
                                        <td>
                                            <div class="btn-table-align">
                                                <span v-if="activePlan(user)">
                                                    <a :href="customerUrlOnBillingProvider(user)" target="_blank">
                                                        @{{ activePlan(user).name }} (@{{ activePlan(user).interval | capitalize }})
                                                    </a>
                                                </span>

                                                <span v-else>
                                                    {{__('None')}}
                                                </span>
                                            </div>
                                        </td>

                                        <!-- View User Profile -->
                                        <td>
                                            <button class="btn btn-default" @click="showUserProfile(user)">
                                                <i class="fa fa-search"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</spark-kiosk-profile>
